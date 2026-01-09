<?php

/**
 * AutoBudget.php
 * Copyright (c) 2020 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Models;

use Deprecated;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AutoBudget
 *
 * Configura orcamentos automaticos que sao renovados periodicamente.
 * Permite definir valores que sao automaticamente alocados para orcamentos
 * em intervalos regulares (diario, semanal, mensal, etc.).
 *
 * @property int                      $id                      Identificador unico do auto-orcamento
 * @property int                      $budget_id               ID do orcamento associado
 * @property int                      $transaction_currency_id ID da moeda
 * @property int                      $auto_budget_type        Tipo de auto-orcamento (reset, rollover, adjusted)
 * @property string                   $amount                  Valor do orcamento automatico
 * @property string                   $native_amount           Valor na moeda nativa
 * @property string                   $period                  Periodo de renovacao
 * @property \Carbon\Carbon|null      $deleted_at              Data de exclusao (soft delete)
 * @property-read Budget              $budget                  Orcamento associado
 * @property-read TransactionCurrency $transactionCurrency     Moeda do orcamento
 */
class AutoBudget extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    #[Deprecated] /** @deprecated */
    public const int AUTO_BUDGET_ADJUSTED = 3;

    #[Deprecated] /** @deprecated */
    public const int AUTO_BUDGET_RESET    = 1;

    #[Deprecated] /** @deprecated */
    public const int AUTO_BUDGET_ROLLOVER = 2;
    protected $casts
                                          = [
            'amount'        => 'string',
            'native_amount' => 'string',
        ];
    protected $fillable                   = ['budget_id', 'amount', 'period', 'native_amount'];

    /**
     * Retorna o orcamento associado a este auto-orcamento.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Budget
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Retorna a moeda associada a este auto-orcamento.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Accessor para garantir que o valor seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor do orcamento
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do orcamento seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do orcamento
     */
    protected function budgetId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            // 'auto_budget_type' => AutoBudgetType::class,
        ];
    }

    /**
     * Accessor para garantir que o ID da moeda seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da moeda
     */
    protected function transactionCurrencyId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }
}
