<?php

namespace Tests\Unit\Shared\Query;

use App\Features\Shared\Query\BuilderFilter;
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryModifierCategory;
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

    public function test_where_any_like_applies_or_conditions(): void
    {
        $builder = QueryFilter::whereAnyLike(
            fields: ['name', 'email', 'company'],
            value: 'Ana',
        )->apply($this->builder());

        $sql = $builder->toSql();

        $this->assertStringContainsString('"name" like ?', $sql);
        $this->assertStringContainsString('"email" like ?', $sql);
        $this->assertStringContainsString('"company" like ?', $sql);
        $this->assertSame(['%Ana%', '%Ana%', '%Ana%'], $builder->getBindings());
        $this->assertSame(QueryModifierCategory::FILTER, QueryFilter::whereAnyLike(['name'], 'x')->category());
    }

    public function test_order_by_accepts_asc(): void
    {
        $builder = QueryOptions::orderBy('name', 'asc')->apply($this->builder());

        $this->assertStringContainsString('order by "name" asc', $builder->toSql());
        $this->assertSame(QueryModifierCategory::OPTION, QueryOptions::orderBy('name')->category());
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

    public function test_builder_filter_applies_query_filter(): void
    {
        $builder = (new BuilderFilter)($this->builder(), [
            QueryFilter::where('org_id', 5),
        ]);

        $this->assertStringContainsString('"org_id" = ?', $builder->toSql());
    }

    public function test_builder_filter_applies_query_options(): void
    {
        $builder = (new BuilderFilter)($this->builder(), [
            QueryOptions::limit(25),
        ]);

        $this->assertSame(25, $builder->getQuery()->limit);
    }

    public function test_builder_filter_accepts_both_in_same_array(): void
    {
        $builder = (new BuilderFilter)($this->builder(), [
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
    }

    public function test_builder_filter_applies_only_filters_for_filter_category(): void
    {
        $builder = (new BuilderFilter)(
            builder: $this->builder(),
            modifiers: [
                QueryFilter::where('org_id', 5),
                QueryOptions::orderBy('name', 'desc'),
                QueryOptions::limit(10),
            ],
            category: QueryModifierCategory::FILTER,
        );

        $sql = $builder->toSql();

        $this->assertStringContainsString('"org_id" = ?', $sql);
        $this->assertStringNotContainsString('order by', $sql);
        $this->assertNull($builder->getQuery()->limit);
    }

    public function test_builder_filter_applies_only_options_for_option_category(): void
    {
        $builder = (new BuilderFilter)(
            builder: $this->builder(),
            modifiers: [
                QueryFilter::where('org_id', 5),
                QueryOptions::orderBy('name', 'asc'),
                QueryOptions::limit(7),
            ],
            category: QueryModifierCategory::OPTION,
        );

        $sql = $builder->toSql();

        $this->assertStringNotContainsString('"org_id" = ?', $sql);
        $this->assertStringContainsString('order by "name" asc', $sql);
        $this->assertSame(7, $builder->getQuery()->limit);
        $this->assertSame([], $builder->getBindings());
    }

    public function test_builder_filter_applies_all_when_category_is_null(): void
    {
        $builder = (new BuilderFilter)(
            builder: $this->builder(),
            modifiers: [
                QueryFilter::where('org_id', 5),
                QueryOptions::limit(3),
            ],
            category: null,
        );

        $this->assertStringContainsString('"org_id" = ?', $builder->toSql());
        $this->assertSame(3, $builder->getQuery()->limit);
    }

    public function test_builder_filter_ignores_invalid_elements(): void
    {
        $builder = (new BuilderFilter)($this->builder(), [
            null,
            'texto',
            123,
            ['field' => 'org_id'],
            new \stdClass,
            QueryFilter::where('org_id', 5),
        ]);

        $this->assertStringContainsString('"org_id" = ?', $builder->toSql());
        $this->assertSame([5], $builder->getBindings());
    }

    public function test_builder_filter_preserves_received_order(): void
    {
        $builder = (new BuilderFilter)($this->builder(), [
            QueryOptions::orderBy('name', 'asc'),
            QueryOptions::orderBy('created_at', 'desc'),
        ]);

        $this->assertStringContainsString(
            'order by "name" asc, "created_at" desc',
            $builder->toSql(),
        );
    }

    public function test_builder_filter_returns_a_builder(): void
    {
        $builder = (new BuilderFilter)($this->builder(), [
            QueryFilter::where('org_id', 5),
            QueryOptions::limit(10),
        ]);

        $this->assertInstanceOf(Builder::class, $builder);
    }

    public function test_builder_filter_does_not_execute_query(): void
    {
        $executed = [];

        Client::getConnectionResolver()
            ->connection()
            ->listen(function ($query) use (&$executed): void {
                $executed[] = $query->sql;
            });

        (new BuilderFilter)($this->builder(), [
            QueryFilter::where('org_id', 5),
            QueryOptions::orderBy('created_at', 'desc'),
            QueryOptions::offset(20),
            QueryOptions::limit(10),
        ]);

        $this->assertSame([], $executed);
    }
}
