<?php

namespace Tests\Unit\Shared\Query;

use App\Features\Shared\Query\ApplyQueryModifiers;
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class QueryModifiersTest extends TestCase
{
    private function builder(): Builder
    {
        return Client::query();
    }

    public function test_where_applies_filter(): void
    {
        $builder = QueryFilter::where('org_id', 5)->apply($this->builder());

        $this->assertStringContainsString('"org_id" = ?', $builder->toSql());
        $this->assertSame([5], $builder->getBindings());
    }

    public function test_where_accepts_custom_comparisons(): void
    {
        foreach (['!=', '>=', '<=', 'like'] as $comparison) {
            $builder = QueryFilter::where('name', 'x', $comparison)->apply($this->builder());

            $this->assertStringContainsString("\"name\" {$comparison} ?", $builder->toSql());
            $this->assertSame(['x'], $builder->getBindings());
        }
    }

    public function test_where_in_applies_where_in(): void
    {
        $builder = QueryFilter::whereIn('status', ['active', 'pending'])->apply($this->builder());

        $this->assertStringContainsString('"status" in (?, ?)', $builder->toSql());
        $this->assertSame(['active', 'pending'], $builder->getBindings());
    }

    public function test_where_not_in_applies_where_not_in(): void
    {
        $builder = QueryFilter::whereNotIn('status', ['cancelled'])->apply($this->builder());

        $this->assertStringContainsString('"status" not in (?)', $builder->toSql());
        $this->assertSame(['cancelled'], $builder->getBindings());
    }

    public function test_where_between_applies_where_between(): void
    {
        $builder = QueryFilter::whereBetween('created_at', '2026-01-01', '2026-12-31')
            ->apply($this->builder());

        $this->assertStringContainsString('"created_at" between ? and ?', $builder->toSql());
        $this->assertSame(['2026-01-01', '2026-12-31'], $builder->getBindings());
    }

    public function test_where_null_applies_where_null(): void
    {
        $builder = QueryFilter::whereNull('deleted_at')->apply($this->builder());

        $this->assertStringContainsString('"deleted_at" is null', $builder->toSql());
    }

    public function test_where_not_null_applies_where_not_null(): void
    {
        $builder = QueryFilter::whereNotNull('email')->apply($this->builder());

        $this->assertStringContainsString('"email" is not null', $builder->toSql());
    }

    public function test_order_by_accepts_asc(): void
    {
        $builder = QueryOptions::orderBy('name', 'asc')->apply($this->builder());

        $this->assertStringContainsString('order by "name" asc', $builder->toSql());
    }

    public function test_order_by_accepts_desc(): void
    {
        $builder = QueryOptions::orderBy('created_at', 'desc')->apply($this->builder());

        $this->assertStringContainsString('order by "created_at" desc', $builder->toSql());
    }

    public function test_invalid_direction_becomes_asc(): void
    {
        $builder = QueryOptions::orderBy('name', 'sideways')->apply($this->builder());

        $this->assertStringContainsString('order by "name" asc', $builder->toSql());
    }

    public function test_negative_offset_becomes_zero(): void
    {
        $builder = QueryOptions::offset(-10)->apply($this->builder());

        $this->assertSame(0, $builder->getQuery()->offset);
    }

    public function test_limit_below_one_becomes_one(): void
    {
        $builder = QueryOptions::limit(0)->apply($this->builder());

        $this->assertSame(1, $builder->getQuery()->limit);
    }

    public function test_apply_query_modifiers_accepts_query_filter(): void
    {
        $builder = (new ApplyQueryModifiers)($this->builder(), [
            QueryFilter::where('org_id', 5),
        ]);

        $this->assertStringContainsString('"org_id" = ?', $builder->toSql());
    }

    public function test_apply_query_modifiers_accepts_query_options(): void
    {
        $builder = (new ApplyQueryModifiers)($this->builder(), [
            QueryOptions::limit(25),
        ]);

        $this->assertSame(25, $builder->getQuery()->limit);
    }

    public function test_filters_and_options_can_be_mixed(): void
    {
        $builder = (new ApplyQueryModifiers)($this->builder(), [
            QueryFilter::where('org_id', 5),
            QueryFilter::whereIn('status', ['active', 'pending']),
            QueryOptions::orderBy('created_at', 'desc'),
            QueryOptions::offset(20),
            QueryOptions::limit(10),
        ]);

        $sql = $builder->toSql();

        $this->assertStringContainsString('"org_id" = ?', $sql);
        $this->assertStringContainsString('"status" in (?, ?)', $sql);
        $this->assertStringContainsString('order by "created_at" desc', $sql);
        $this->assertSame(20, $builder->getQuery()->offset);
        $this->assertSame(10, $builder->getQuery()->limit);
        $this->assertSame([5, 'active', 'pending'], $builder->getBindings());
    }

    public function test_invalid_elements_are_ignored(): void
    {
        $builder = (new ApplyQueryModifiers)($this->builder(), [
            'texto',
            123,
            null,
            new \stdClass,
            ['field' => 'org_id', 'operator' => 'where', 'value' => 1],
            QueryFilter::where('org_id', 5),
        ]);

        $this->assertStringContainsString('"org_id" = ?', $builder->toSql());
        $this->assertSame([5], $builder->getBindings());
    }

    public function test_modifiers_apply_in_received_order(): void
    {
        $builder = (new ApplyQueryModifiers)($this->builder(), [
            QueryOptions::orderBy('name', 'asc'),
            QueryOptions::orderBy('created_at', 'desc'),
        ]);

        $this->assertStringContainsString(
            'order by "name" asc, "created_at" desc',
            $builder->toSql(),
        );
    }

    public function test_result_is_still_a_builder(): void
    {
        $builder = (new ApplyQueryModifiers)($this->builder(), [
            QueryFilter::where('org_id', 5),
            QueryOptions::limit(10),
        ]);

        $this->assertInstanceOf(Builder::class, $builder);
    }

    public function test_no_query_is_executed(): void
    {
        $executed = [];

        Client::getConnectionResolver()
            ->connection()
            ->listen(function ($query) use (&$executed): void {
                $executed[] = $query->sql;
            });

        (new ApplyQueryModifiers)($this->builder(), [
            QueryFilter::where('org_id', 5),
            QueryFilter::whereIn('status', ['active']),
            QueryOptions::orderBy('created_at', 'desc'),
            QueryOptions::offset(20),
            QueryOptions::limit(10),
        ]);

        $this->assertSame([], $executed);
    }
}
