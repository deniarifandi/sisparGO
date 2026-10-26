<?php echo view('header'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong>Chat with:</strong>
            <?= esc($room['anggota_nama']) ?>
        </div>
        <a href="<?= site_url('chat-room') ?>" class="btn btn-sm btn-secondary">
            Back
        </a>
    </div>

    <div class="card-body" id="chatBox"
         style="height: 420px; overflow-y: auto; background:#f8f9fa;">
        <!-- messages loaded here -->
    </div>

    <div class="card-footer">
        <div class="input-group">
            <textarea
                   id="messageInput"
                   class="form-control"
                   placeholder="Type a message..."
                   maxlength="1000"
                   rows="2"></textarea>
            <button id="sendBtn" class="btn btn-primary">
                Send
            </button>
        </div>
        <small id="sendStatus" class="d-block mt-2" role="status" aria-live="polite"></small>
    </div>
</div>

<script>
const roomId  = <?= (int) $room['id'] ?>;
const adminId = <?= (int) session()->get('user_id') ?>;
const chatBox = $('#chatBox');

/**
 * Load messages
 */
let isSending = false;
let lastDateKey = null;
let lastRenderedMessages = null;
let hasLoadedMessages = false;

function messageDateKey(createdAt) {
    const dateKey = String(createdAt || '').match(/^\d{4}-\d{2}-\d{2}/);
    if (dateKey) return dateKey[0];
    const today = new Date();
    return [today.getFullYear(), String(today.getMonth() + 1).padStart(2, '0'),
        String(today.getDate()).padStart(2, '0')].join('-');
}

function messageTime(createdAt) {
    const time = String(createdAt || '').match(/[ T](\d{2}):(\d{2})/);
    if (time) return `${time[1]}:${time[2]}`;
    const now = new Date();
    return `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
}

function appendDateSeparator(dateKey) {
    if (dateKey === lastDateKey) return;
    lastDateKey = dateKey;
    const [year, month, day] = dateKey.split('-').map(Number);
    const label = new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    }).format(new Date(year, month - 1, day));
    chatBox.append($('<div>')
        .addClass('text-center my-3')
        .append($('<span>')
            .addClass('px-3 py-1 rounded-pill')
            .css({ background: '#e9ecef', color: '#6c757d', fontSize: '12px' })
            .text(label)));
}

function appendMessage(message, isAdmin, pending = false, createdAt = null) {
    appendDateSeparator(messageDateKey(createdAt));
    const bubble = $('<div>')
        .css({
            maxWidth: '70%',
            padding: '8px 12px',
            borderRadius: '12px',
            fontSize: '13px',
            overflowWrap: 'anywhere',
            background: isAdmin ? '#0d6efd' : '#e9ecef',
            color: isAdmin ? '#fff' : '#000'
        });
    bubble.append($('<div>').css({ whiteSpace: 'pre-wrap' }).text(message));
    const footer = $('<div>')
        .addClass('d-flex justify-content-end align-items-center')
        .css({ gap: '5px', marginTop: '3px', fontSize: '11px', lineHeight: '1', opacity: 0.8 })
        .append($('<span>').text(messageTime(createdAt)));
    if (isAdmin) {
        footer.append($('<span>')
            .addClass('message-state')
            .css({ fontSize: '13px' })
            .attr('aria-label', pending ? 'Sedang mengirim' : 'Tersimpan di server')
            .text(pending ? '🕒' : '✓'));
    }
    bubble.append(footer);
    const row = $('<div>')
        .addClass('mb-2 d-flex')
        .addClass(isAdmin ? 'justify-content-end' : 'justify-content-start')
        .append(bubble);
    chatBox.append(row);
    return row;
}

function loadMessages() {
    if (isSending || sendQueue.length) return;
    $.get("<?= site_url('chat-room') ?>/" + roomId + "/messages", function (data) {
        if (isSending || sendQueue.length) return;
        const signature = JSON.stringify(data);
        if (signature === lastRenderedMessages) return;
        const box = chatBox[0];
        const wasNearBottom = !hasLoadedMessages ||
            box.scrollHeight - box.scrollTop - box.clientHeight < 60;
        const previousScrollTop = box.scrollTop;
        chatBox.html('');
        lastDateKey = null;
        data.forEach(function (msg) {
            appendMessage(msg.message, msg.sender_type === 'admin', false, msg.created_at);
        });
        lastRenderedMessages = signature;
        hasLoadedMessages = true;
        chatBox.scrollTop(wasNearBottom ? box.scrollHeight : previousScrollTop);
    });
}

/**
 * Send message
 */
const sendBtn = $('#sendBtn');
const messageInput = $('#messageInput');
const sendStatus = $('#sendStatus');
const sendQueue = [];
let nextSendAt = 0;
let retryUntil = 0;
let queueTimer = null;
let lastSendError = '';

function showQueueStatus(waitSeconds = 0) {
    if (lastSendError) {
        sendStatus.addClass('text-danger').text(lastSendError);
    } else if (sendQueue.length) {
        sendStatus.removeClass('text-danger').text(waitSeconds > 0
            ? `${sendQueue.length} pesan antre, mengirim lagi dalam ${waitSeconds} detik. Jangan tutup halaman.`
            : `${sendQueue.length} pesan sedang dikirim. Jangan tutup halaman.`);
    } else {
        sendStatus.removeClass('text-danger').text('');
    }
}

function pumpQueue() {
    if (queueTimer) {
        clearTimeout(queueTimer);
        queueTimer = null;
    }
    if (isSending || !sendQueue.length) {
        showQueueStatus();
        return;
    }
    const wait = Math.max(nextSendAt, retryUntil) - Date.now();
    if (wait > 0) {
        showQueueStatus(Math.ceil(wait / 1000));
        queueTimer = setTimeout(pumpQueue, Math.min(wait + 20, 500));
        return;
    }

    isSending = true;
    const item = sendQueue[0];
    const startedAt = Date.now();
    showQueueStatus();
    $.post("<?= site_url('chat-room/send') ?>", {
        room_id: roomId,
        message: item.message
    }, null, 'json')
        .done(function (res) {
            if (!res.status) {
                item.row.find('.message-state')
                    .attr('aria-label', 'Pesan gagal dikirim').text('!');
                lastSendError = 'Pesan gagal dikirim. Periksa daftar pesan sebelum mencoba lagi.';
            } else {
                item.row.find('.message-state')
                    .attr('aria-label', 'Tersimpan di server').text('✓');
            }
            sendQueue.shift();
        })
        .fail(function (xhr) {
            if (xhr.status === 429) {
                const retryAfter = parseInt(
                    xhr.responseJSON?.retry_after || xhr.getResponseHeader('Retry-After'), 10
                ) || 2;
                retryUntil = Date.now() + retryAfter * 1000;
                return;
            }
            item.row.find('.message-state')
                .attr('aria-label', xhr.status === 422 ? 'Pesan ditolak' : 'Status pengiriman belum diketahui')
                .text('!');
            lastSendError = xhr.status === 422
                ? (xhr.responseJSON?.message || 'Pesan ditolak server.')
                : 'Koneksi gagal. Periksa daftar pesan sebelum mencoba lagi.';
            sendQueue.shift();
        })
        .always(function () {
            nextSendAt = Math.max(nextSendAt, startedAt + 2100);
            isSending = false;
            if (!sendQueue.length) {
                lastRenderedMessages = null;
                loadMessages();
            }
            pumpQueue();
        });
}

sendBtn.on('click', function () {
    const message = messageInput.val().trim();
    if (!message) return;
    if (message.length > 1000) {
        sendStatus.addClass('text-danger').text('Pesan maksimal 1.000 karakter.');
        return;
    }
    if (sendQueue.length >= 10) {
        sendStatus.addClass('text-danger').text('Antrean penuh. Tunggu pesan sebelumnya terkirim.');
        return;
    }

    lastSendError = '';
    const row = appendMessage(message, true, true);
    sendQueue.push({ message, row });
    messageInput.val('').trigger('focus');
    chatBox.scrollTop(chatBox[0].scrollHeight);
    pumpQueue();
});

/**
 * Enter sends; Shift+Enter adds a new line.
 */
$('#messageInput').on('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
        e.preventDefault();
        $('#sendBtn').click();
    }
});

window.addEventListener('beforeunload', function (event) {
    if (sendQueue.length) {
        event.preventDefault();
        event.returnValue = '';
    }
});

/**
 * Initial load + polling
 */
loadMessages();
setInterval(loadMessages, 3000);
</script>

<?php echo view('footer'); ?>
