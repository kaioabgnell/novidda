<?php

namespace App\Services;

use App\Models\Changelog;
use Carbon\Carbon;

class SegmentMatcher
{
    public function matches(Changelog $changelog, ?array $user): bool
    {
        if (!$changelog->segment_enabled) {
            return true;
        }

        if (empty($user)) {
            return false;
        }

        foreach ($changelog->segmentRules as $rule) {
            $userValue = data_get($user, $rule->attribute);
            if (!$this->evaluate($userValue, $rule->operator, $rule->value)) {
                return false;
            }
        }

        return true;
    }

    public function matchesRaw(array $snapshot, array $rules): bool
    {
        foreach ($rules as $rule) {
            $userValue = data_get($snapshot, $rule['attribute']);
            if (!$this->evaluate($userValue, $rule['operator'], $rule['value'])) {
                return false;
            }
        }

        return true;
    }

    private function evaluate($userValue, string $operator, $ruleValue): bool
    {
        return match ($operator) {
            'equals'      => $this->loose($userValue) === $this->loose($ruleValue),
            'not_equals'  => $this->loose($userValue) !== $this->loose($ruleValue),
            'contains'    => is_string($userValue) && is_string($ruleValue)
                             && str_contains(mb_strtolower($userValue), mb_strtolower($ruleValue)),
            'starts_with' => is_string($userValue) && is_string($ruleValue)
                             && str_starts_with(mb_strtolower($userValue), mb_strtolower($ruleValue)),
            'ends_with'   => is_string($userValue) && is_string($ruleValue)
                             && str_ends_with(mb_strtolower($userValue), mb_strtolower($ruleValue)),
            'greater_than'=> is_numeric($userValue) && is_numeric($ruleValue)
                             && (float) $userValue > (float) $ruleValue,
            'less_than'   => is_numeric($userValue) && is_numeric($ruleValue)
                             && (float) $userValue < (float) $ruleValue,
            'before'      => $userValue && $ruleValue
                             && Carbon::parse($userValue)->lt(Carbon::parse($ruleValue)),
            'after'       => $userValue && $ruleValue
                             && Carbon::parse($userValue)->gt(Carbon::parse($ruleValue)),
            'in'          => is_array($ruleValue) && in_array($userValue, $ruleValue),
            'not_in'      => is_array($ruleValue) && !in_array($userValue, $ruleValue),
            'exists'      => $userValue !== null,
            'not_exists'  => $userValue === null,
            default       => false,
        };
    }

    private function loose($value): mixed
    {
        if (is_string($value)) {
            $lower = strtolower($value);
            if ($lower === 'true') return true;
            if ($lower === 'false') return false;
        }

        return $value;
    }
}
