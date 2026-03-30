<?php

namespace App\Filters;

use DeepCopy\Exception\PropertyException;
use Illuminate\Http\Request;

use function PHPUnit\Framework\stringContains;

abstract class Filter
{
    protected array $allowedOperatorsFields = [];

    protected array $translateOperatorsFields = [
        'gt' => '>',
        'lt' => '<',
        'gte' => '>=',
        'lte' => '<=',
        'eq' => '=',
        'ne' => '!=',
        'in' => 'in',
    ];

    public function filter(Request $request)
    {
        $where = [];
        $whereIn = [];

        if (empty($this->allowedOperatorsFields)) {
            throw new PropertyException('No allowed operators defined for filtering.');
        }

        foreach ($this->allowedOperatorsFields as $param => $operators) {
            $queryOperator = $request->query($param);
            if ($queryOperator) {
                foreach ($queryOperator as $operator => $value) {
                    if (!in_array($operator, $operators)) {
                        throw new PropertyException("Operator '{$operator}' is not allowed for parameter '{$param}'.");
                    }
                    if (!stringContains($value, '[')) {
                        $whereIn[] = [
                            $param,
                            explode(',', str_replace(['[', ']'], ['', ''], $value)),
                            $value
                        ];
                    }else{
                        $where[] = [
                            $param,
                            $this->translateOperatorsFields[$operator] ?? $operator,
                            $value
                        ];
                    };
                }
            }
        }

        if(empty($where) && empty($whereIn)){
            return [];
        };

        return [
            'where' => $where,
            'whereIn' => $whereIn
        ];
    }
}
