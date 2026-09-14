<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Pos\CashVarianceExplanationRepository;
use App\Application\Pos\CashVarianceReviewDecisionRepository;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\OperatePosCashVarianceReconciliation;
use App\Application\Pos\PosCashVarianceReconciliationWorkspaceRepository;
use App\Application\Pos\RecordCashVarianceExplanation;
use App\Application\Pos\RecordCashVarianceReviewDecision;
use App\Application\Pos\ShiftOpeningClock;
use App\Application\Pos\ViewPosCashVarianceReconciliationWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosCashVarianceExplanationController;
use App\Delivery\Http\Pos\PosCashVarianceReconciliationWorkspaceController;
use App\Delivery\Http\Pos\PosCashVarianceReviewDecisionController;
use App\Infrastructure\Pos\LaravelCashVarianceExplanationRepository;
use App\Infrastructure\Pos\LaravelCashVarianceReviewDecisionRepository;
use App\Infrastructure\Pos\LaravelExpectedCashSnapshotReader;
use App\Infrastructure\Pos\LaravelPosCashVarianceReconciliationWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosCashVarianceReconciliationWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosCashVarianceReconciliationWorkspaceRepository::class, function ($app): PosCashVarianceReconciliationWorkspaceRepository {
            return new LaravelPosCashVarianceReconciliationWorkspaceRepository(
                $this->connection($app),
                $app->make(LaravelExpectedCashSnapshotReader::class),
                $app->make(DeriveCashVariance::class),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->featureEnabled(),
                (bool) config('oneqay.pos_shift_closing_cash_evidence.enabled', false),
            );
        });

        $this->app->scoped(CashVarianceExplanationRepository::class, function ($app): CashVarianceExplanationRepository {
            return new LaravelCashVarianceExplanationRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->featureEnabled(),
            );
        });
        $this->app->scoped(RecordCashVarianceExplanation::class, fn ($app): RecordCashVarianceExplanation => new RecordCashVarianceExplanation(
            $app->make(CashVarianceExplanationRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
            $app->make(PersistenceTransaction::class),
            $app->make(ShiftOpeningClock::class),
        ));

        $this->app->scoped(CashVarianceReviewDecisionRepository::class, function ($app): CashVarianceReviewDecisionRepository {
            return new LaravelCashVarianceReviewDecisionRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->featureEnabled(),
            );
        });
        $this->app->scoped(RecordCashVarianceReviewDecision::class, fn ($app): RecordCashVarianceReviewDecision => new RecordCashVarianceReviewDecision(
            $app->make(CashVarianceReviewDecisionRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
            $app->make(PersistenceTransaction::class),
            $app->make(ShiftOpeningClock::class),
        ));

        $this->app->scoped(ViewPosCashVarianceReconciliationWorkspace::class, fn ($app): ViewPosCashVarianceReconciliationWorkspace => new ViewPosCashVarianceReconciliationWorkspace(
            $app->make(PosCashVarianceReconciliationWorkspaceRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
        ));
        $this->app->scoped(OperatePosCashVarianceReconciliation::class, fn ($app): OperatePosCashVarianceReconciliation => new OperatePosCashVarianceReconciliation(
            $app->make(PosCashVarianceReconciliationWorkspaceRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(RecordCashVarianceExplanation::class),
            $app->make(RecordCashVarianceReviewDecision::class),
        ));
    }

    public function boot(): void
    {
        if (! $this->deliveryEnabled()) {
            return;
        }

        Route::get('/pos/shifts/reconciliation/{closing_evidence_id?}', PosCashVarianceReconciliationWorkspaceController::class)
            ->where('closing_evidence_id', '[A-Za-z0-9][A-Za-z0-9._:-]{7,127}')
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.shifts.reconciliation.workspace');

        Route::post('/pos/shifts/reconciliation/explanation', PosCashVarianceExplanationController::class)
            ->middleware(['session.active', 'throttle:10,1', 'throttle:100,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.shifts.reconciliation.explanation');

        Route::post('/pos/shifts/reconciliation/review', PosCashVarianceReviewDecisionController::class)
            ->middleware(['session.active', 'throttle:10,1', 'throttle:100,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.shifts.reconciliation.review');
    }

    private function connection($app): Connection
    {
        /** @var Connection $connection */
        $connection = $app->make('db')->connection();
        return $connection;
    }

    private function persistenceEnabled(): bool
    {
        return (bool) config('database.oneqay_persistence_enabled', false);
    }

    private function runtimeClass(): string
    {
        return (string) config('oneqay.runtime_class', '');
    }

    private function featureEnabled(): bool
    {
        return (bool) config('pos_cash_variance_reconciliation.enabled', false);
    }

    private function deliveryEnabled(): bool
    {
        return in_array(strtolower(trim($this->runtimeClass())), ['local', 'test', 'ci'], true)
            && $this->persistenceEnabled()
            && (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200
            && (int) config('oneqay.session_control.absolute_ttl_seconds', 0) === 43200
            && (bool) config('oneqay.pos_shift_closing_cash_evidence.enabled', false)
            && filter_var(env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false), FILTER_VALIDATE_BOOL)
            && $this->featureEnabled();
    }
}
