<?php

namespace Agencia\Close\Helpers\DataTable;

use Agencia\Close\Helpers\Result;
use Agencia\Close\Conn\Read;

class DataTableHelper
{
    private array $columns = [];
    private array $filters = [];
    private array $searchableColumns = [];
    private array $orderableColumns = [];
    private string $table;
    private string $primaryKey = 'id';
    private array $joins = [];
    private array $whereConditions = [];
    private array $groupBy = [];
    private array $having = [];
    private int $defaultLimit = 10;
    private array $limitOptions = [10, 25, 50, 100];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function addColumn(string $name, string $label, string $type = 'text', array $options = []): self
    {
        $this->columns[$name] = [
            'label' => $label,
            'type' => $type,
            'options' => $options
        ];
        return $this;
    }

    public function addSearchableColumn(string $column): self
    {
        $this->searchableColumns[] = $column;
        return $this;
    }

    public function addOrderableColumn(string $column): self
    {
        $this->orderableColumns[] = $column;
        return $this;
    }

    public function addFilter(string $name, string $label, string $type = 'text', array $options = []): self
    {
        $this->filters[$name] = [
            'label' => $label,
            'type' => $type,
            'options' => $options
        ];
        return $this;
    }

    public function addJoin(string $table, string $condition, string $type = 'LEFT'): self
    {
        $this->joins[] = [
            'table' => $table,
            'condition' => $condition,
            'type' => $type
        ];
        return $this;
    }

    public function addWhereCondition(string $condition, array $params = []): self
    {
        $this->whereConditions[] = [
            'condition' => $condition,
            'params' => $params
        ];
        return $this;
    }

    public function addGroupBy(string $column): self
    {
        $this->groupBy[] = $column;
        return $this;
    }

    public function addHaving(string $condition): self
    {
        $this->having[] = $condition;
        return $this;
    }

    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    public function setDefaultLimit(int $limit): self
    {
        $this->defaultLimit = $limit;
        return $this;
    }

    public function setLimitOptions(array $options): self
    {
        $this->limitOptions = $options;
        return $this;
    }

    public function getData(array $request): array
    {
        $page = (int)($request['page'] ?? 1);
        $limit = (int)($request['limit'] ?? $this->defaultLimit);
        $search = $request['search'] ?? '';
        $orderBy = $request['order_by'] ?? $this->primaryKey;
        $orderDir = $request['order_dir'] ?? 'DESC';
        $rawFilters = $request['filters'] ?? [];

        // Processar filtros - extrair apenas os valores reais
        $filters = [];
        foreach ($rawFilters as $filterName => $filterData) {
            if (is_array($filterData) && isset($filterData['value'])) {
                // Se o filtro tem estrutura completa, pegar apenas o valor
                $filters[$filterName] = $filterData['value'];
            } elseif (is_scalar($filterData)) {
                // Se é um valor simples
                $filters[$filterName] = $filterData;
            } elseif (is_array($filterData) && isset($filterData['start']) || isset($filterData['end'])) {
                // Se é um date_range
                $filters[$filterName] = $filterData;
            }
        }

        // Debug temporário
        error_log("DataTable getData - Request: " . json_encode($request));
        error_log("DataTable getData - Raw Filters: " . json_encode($rawFilters));
        error_log("DataTable getData - Processed Filters: " . json_encode($filters));

        // Construir query base
        $select = $this->buildSelect();
        $from = $this->buildFrom();
        $where = $this->buildWhere($search, $filters);
        $groupBy = $this->buildGroupBy();
        $having = $this->buildHaving();
        $orderBy = $this->buildOrderBy($orderBy, $orderDir);

        // Query para contar total de registros
        $countQuery = "SELECT COUNT(DISTINCT {$this->table}.{$this->primaryKey}) as total {$from} {$where} {$groupBy} {$having}";
        
        // Query para buscar dados
        $dataQuery = "{$select} {$from} {$where} {$groupBy} {$having} {$orderBy} LIMIT " . (($page - 1) * $limit) . ", {$limit}";

        // Debug temporário
        error_log("DataTable getData - Count Query: " . $countQuery);
        error_log("DataTable getData - Data Query: " . $dataQuery);

        // Executar queries
        $read = new Read();
        
        // Contar total
        $params = $this->buildParams($search, $filters);
        error_log("DataTable getData - Params: " . $params);
        
        $read->FullRead($countQuery, $params);
        $totalResult = $read->getResult();
        $total = $totalResult ? (int)$totalResult[0]['total'] : 0;

        // Buscar dados
        $read->FullRead($dataQuery, $params);
        $data = $read->getResult() ?? [];

        // Calcular paginação
        $totalPages = ceil($total / $limit);
        $hasNext = $page < $totalPages;
        $hasPrev = $page > 1;

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $hasNext,
                'has_prev' => $hasPrev,
                'from' => (($page - 1) * $limit) + 1,
                'to' => min($page * $limit, $total)
            ],
            'filters' => $this->filters,
            'columns' => $this->columns,
            'searchable_columns' => $this->searchableColumns,
            'orderable_columns' => $this->orderableColumns,
            'limit_options' => $this->limitOptions
        ];
    }

    public function getDataForExport(array $request): array
    {
        $search = $request['search'] ?? '';
        $orderBy = $request['order_by'] ?? $this->primaryKey;
        $orderDir = $request['order_dir'] ?? 'DESC';
        $rawFilters = $request['filters'] ?? [];

        // Processar filtros - extrair apenas os valores reais
        $filters = [];
        foreach ($rawFilters as $filterName => $filterData) {
            if (is_array($filterData) && isset($filterData['value'])) {
                // Se o filtro tem estrutura completa, pegar apenas o valor
                $filters[$filterName] = $filterData['value'];
            } elseif (is_scalar($filterData)) {
                // Se é um valor simples
                $filters[$filterName] = $filterData;
            } elseif (is_array($filterData) && isset($filterData['start']) || isset($filterData['end'])) {
                // Se é um date_range
                $filters[$filterName] = $filterData;
            }
        }

        // Construir query base
        $select = $this->buildSelect();
        $from = $this->buildFrom();
        $where = $this->buildWhere($search, $filters);
        $groupBy = $this->buildGroupBy();
        $having = $this->buildHaving();
        $orderBy = $this->buildOrderBy($orderBy, $orderDir);

        // Query para buscar todos os dados (sem LIMIT)
        $dataQuery = "{$select} {$from} {$where} {$groupBy} {$having} {$orderBy}";

        // Executar query
        $read = new Read();
        $params = $this->buildParams($search, $filters);
        $read->FullRead($dataQuery, $params);
        $data = $read->getResult() ?? [];

        return [
            'data' => $data,
            'columns' => $this->columns
        ];
    }

    public function generateCSV(array $data, array $columns): string
    {
        // Preparar cabeçalhos
        $headers = [];
        foreach ($columns as $name => $column) {
            if ($name !== 'actions') { // Excluir coluna de ações
                $headers[] = $column['label'];
            }
        }

        // Preparar dados
        $csvData = [];
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($columns as $name => $column) {
                if ($name !== 'actions') { // Excluir coluna de ações
                    $value = $row[$name] ?? '';
                    
                    // Formatar valores especiais
                    switch ($column['type']) {
                        case 'select':
                            if (isset($column['options']['options'][$value])) {
                                $value = $column['options']['options'][$value];
                            }
                            break;
                        case 'datetime':
                            if (!empty($value)) {
                                $value = date('d/m/Y H:i:s', strtotime($value));
                            }
                            break;
                        case 'date':
                            if (!empty($value)) {
                                $value = date('d/m/Y', strtotime($value));
                            }
                            break;
                    }
                    
                    $csvRow[] = $value;
                }
            }
            $csvData[] = $csvRow;
        }

        // Gerar CSV com encoding correto
        $output = '';
        
        // Escrever cabeçalhos
        $output .= implode(';', array_map(function($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers)) . "\r\n";
        
        // Escrever dados
        foreach ($csvData as $row) {
            $output .= implode(';', array_map(function($cell) {
                return '"' . str_replace('"', '""', $cell) . '"';
            }, $row)) . "\r\n";
        }
        
        // Converter para UTF-8 com BOM
        return "\xEF\xBB\xBF" . $output;
    }

    private function buildSelect(): string
    {
        $selects = ["{$this->table}.*"];
        
        // Adicionar campos específicos das colunas se necessário
        foreach ($this->columns as $name => $column) {
            if (isset($column['options']['select'])) {
                $selects[] = $column['options']['select'];
            }
        }

        $selectClause = "SELECT " . implode(', ', $selects);
        
        // Debug temporário
        error_log("DataTable buildSelect - Selects: " . json_encode($selects));
        error_log("DataTable buildSelect - Final SELECT: " . $selectClause);
        
        return $selectClause;
    }

    private function buildFrom(): string
    {
        $from = "FROM {$this->table}";
        
        foreach ($this->joins as $join) {
            $from .= " {$join['type']} JOIN {$join['table']} ON {$join['condition']}";
        }

        return $from;
    }

    private function buildWhere(string $search, array $filters): string
    {
        $conditions = [];

        // Condições base
        foreach ($this->whereConditions as $where) {
            $conditions[] = $where['condition'];
        }

        // Pesquisa global
        if (!empty($search) && !empty($this->searchableColumns)) {
            $searchConditions = [];
            foreach ($this->searchableColumns as $column) {
                $searchConditions[] = "{$column} LIKE :search";
            }
            $conditions[] = "(" . implode(' OR ', $searchConditions) . ")";
        }

        // Filtros específicos
        foreach ($filters as $filterName => $filterValue) {
            if (!empty($filterValue) && isset($this->filters[$filterName])) {
                $filter = $this->filters[$filterName];
                switch ($filter['type']) {
                    case 'select':
                    case 'text':
                        $conditions[] = "{$filterName} = :filter_{$filterName}";
                        break;
                    case 'like':
                        $conditions[] = "{$filterName} LIKE :filter_{$filterName}";
                        break;
                    case 'date_range':
                        if (isset($filterValue['start']) && !empty($filterValue['start'])) {
                            $conditions[] = "{$filterName} >= :filter_{$filterName}_start";
                        }
                        if (isset($filterValue['end']) && !empty($filterValue['end'])) {
                            $conditions[] = "{$filterName} <= :filter_{$filterName}_end";
                        }
                        break;
                }
            }
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : '';
        
        // Debug temporário
        error_log("DataTable buildWhere - Search: " . $search);
        error_log("DataTable buildWhere - Filters: " . json_encode($filters));
        error_log("DataTable buildWhere - Conditions: " . json_encode($conditions));
        error_log("DataTable buildWhere - Final WHERE: " . $whereClause);
        
        return $whereClause;
    }

    private function buildGroupBy(): string
    {
        return !empty($this->groupBy) ? "GROUP BY " . implode(', ', $this->groupBy) : '';
    }

    private function buildHaving(): string
    {
        return !empty($this->having) ? "HAVING " . implode(' AND ', $this->having) : '';
    }

    private function buildOrderBy(string $orderBy, string $orderDir): string
    {
        if (!in_array($orderBy, $this->orderableColumns) && !in_array($orderBy, [$this->primaryKey])) {
            $orderBy = $this->primaryKey;
        }
        
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
        
        return "ORDER BY {$orderBy} {$orderDir}";
    }

    private function buildParams(string $search, array $filters): string
    {
        $params = [];

        // Parâmetros das condições base
        foreach ($this->whereConditions as $where) {
            if (!empty($where['params']) && is_array($where['params'])) {
                foreach ($where['params'] as $key => $value) {
                    if (is_string($value) && is_scalar($value)) {
                        $params[$key] = $value;
                    }
                }
            }
        }

        // Parâmetro de pesquisa
        if (!empty($search) && is_scalar($search)) {
            $params['search'] = "%{$search}%";
        }

        // Parâmetros dos filtros
        foreach ($filters as $filterName => $filterValue) {
            // Verificar se o valor do filtro é escalar ou um array válido para date_range
            if (empty($filterValue) || !isset($this->filters[$filterName])) {
                continue;
            }
            
            $filter = $this->filters[$filterName];
            
            // Para date_range, verificar se é um array válido
            if ($filter['type'] === 'date_range' && !is_array($filterValue)) {
                continue;
            }
            
            // Para outros tipos, verificar se é escalar
            if ($filter['type'] !== 'date_range' && !is_scalar($filterValue)) {
                continue;
            }
            
            switch ($filter['type']) {
                case 'select':
                case 'text':
                    $params["filter_{$filterName}"] = $filterValue;
                    break;
                case 'like':
                    $params["filter_{$filterName}"] = "%{$filterValue}%";
                    break;
                case 'date_range':
                    if (isset($filterValue['start']) && !empty($filterValue['start']) && is_scalar($filterValue['start'])) {
                        $params["filter_{$filterName}_start"] = $filterValue['start'];
                    }
                    if (isset($filterValue['end']) && !empty($filterValue['end']) && is_scalar($filterValue['end'])) {
                        $params["filter_{$filterName}_end"] = $filterValue['end'];
                    }
                    break;
            }
        }

        return http_build_query($params);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    public function getOrderableColumns(): array
    {
        return $this->orderableColumns;
    }

    public function getLimitOptions(): array
    {
        return $this->limitOptions;
    }
} 