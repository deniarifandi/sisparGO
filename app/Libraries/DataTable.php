<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;

class DataTable
{
    protected $builder;
    protected $request;
    protected $columns;
    protected $sortable = [];

    public function __construct(
        BaseBuilder $builder,
        array $columns = [],
        array $sortable = []
    ) {
        $this->builder  = $builder;
        $this->request  = service('request');
        $this->columns  = $columns;
        $this->sortable = $sortable;
    }

    // Callers with an existing summary may reuse its count before global search.
    public function generate(?int $recordsTotal = null, bool $hasCustomFilter = false)
    {
        $draw = (int) $this->request->getPost('draw');
        $start = (int) $this->request->getPost('start');
        $length = (int) $this->request->getPost('length');

        $search = $this->request->getPost('search');
        $searchValue = is_array($search)
            ? trim($search['value'] ?? '')
            : '';

        /*
        |--------------------------------------------------------------------------
        | 1. TOTAL RECORDS
        |--------------------------------------------------------------------------
        */

        $countBuilder = clone $this->builder;

        $recordsTotal ??= $countBuilder->countAllResults(false);


        /*
        |--------------------------------------------------------------------------
        | 2. SEARCH
        |--------------------------------------------------------------------------
        */

        if ($searchValue !== '' && !empty($this->columns)) {

            $this->builder->groupStart();

            foreach ($this->columns as $col) {

                if (empty($col)) {
                    continue;
                }

                if (
                    stripos($col, 'SUM(') !== false ||
                    stripos($col, 'COUNT(') !== false
                ) {
                    continue;
                }

                $this->builder->orLike($col, $searchValue);
            }

            $this->builder->groupEnd();
        }


        /*
        |--------------------------------------------------------------------------
        | 3. FILTERED RECORDS
        |--------------------------------------------------------------------------
        */

        $filteredBuilder = clone $this->builder;

        $recordsFiltered = ($searchValue !== '' && !empty($this->columns)) || $hasCustomFilter
            ? $filteredBuilder->countAllResults(false)
            : $recordsTotal;


        /*
        |--------------------------------------------------------------------------
        | 4. ORDERING
        |--------------------------------------------------------------------------
        */

        $order = $this->request->getPost('order');

        if (is_array($order)) {

            foreach ($order as $ord) {

                $colIdx = isset($ord['column'])
                    ? (int) $ord['column']
                    : null;

                if ($colIdx === null) {
                    continue;
                }

                $dir = (
                    isset($ord['dir']) &&
                    strtolower($ord['dir']) === 'desc'
                )
                    ? 'DESC'
                    : 'ASC';


                /*
                |--------------------------------------------------------------------------
                | Custom sortable
                |--------------------------------------------------------------------------
                */

                if (isset($this->sortable[$colIdx])) {

                    $this->builder->orderBy(
                        $this->sortable[$colIdx],
                        $dir
                    );

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Default sortable
                |--------------------------------------------------------------------------
                */

                if (isset($this->columns[$colIdx])) {

                    $this->builder->orderBy(
                        $this->columns[$colIdx],
                        $dir
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 5. LIMIT
        |--------------------------------------------------------------------------
        */

        if ($length !== -1) {

            $this->builder->limit(
                $length,
                $start
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 6. FETCH
        |--------------------------------------------------------------------------
        */

        $data = $this->builder
            ->get()
            ->getResultArray();


        /*
        |--------------------------------------------------------------------------
        | 7. RESPONSE
        |--------------------------------------------------------------------------
        */

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }
}
