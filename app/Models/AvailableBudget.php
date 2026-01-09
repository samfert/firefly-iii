<?php

/**
 * AvailableBudget.php
 * Copyright (c) 2019 james@firefly-iii.org
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

use Carbon\Carbon;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AvailableBudget
 *
 * Representa o valor total disponivel para orcamentos em um determinado periodo.
 * Define quanto dinheiro o usuario tem disponivel para alocar em orcamentos
 * durante um intervalo de datas especifico.
 *
 * @property int                      $id                      Identificador unico
 * @property int                      $user_id                 ID do usuario proprietario
 * @property int                      $user_group_id           ID do grupo de usuarios
 * @property int                      $transaction_currency_id ID da moeda
 * @property string                   $amount                  Valor disponivel
 * @property string                   $native_amount           Valor na moeda nativa
 * @property \Carbon\Carbon           $start_date              Data de inicio do periodo
 * @property \Carbon\Carbon           $end_date                Data de fim do periodo
 * @property \Carbon\Carbon           $created_at              Data de criacao
 * @property \Carbon\Carbon           $updated_at              Data de atualizacao
 * @property \Carbon\Carbon|null      $deleted_at              Data de exclusao (soft delete)
 * @property-read User                $user                    Usuario proprietario
 * @property-read TransactionCurrency $transactionCurrency     Moeda do orcamento disponivel
 */
class AvailableBudget extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['user_id', 'user_group_id', 'transaction_currency_id', 'amount', 'start_date', 'end_date', 'start_date_tz', 'end_date_tz', 'native_amount'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $availableBudgetId = (int) $value;

            /** @var User $user */
            $user              = auth()->user();

            /** @var null|AvailableBudget $availableBudget */
            $availableBudget   = $user->availableBudgets()->find($availableBudgetId);
            if (null !== $availableBudget) {
                return $availableBudget;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario deste orcamento disponivel.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna a moeda associada a este orcamento disponivel.
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
     * @return Attribute Atributo computado para o valor disponivel
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor e mutator para a data de fim do periodo.
     * Converte entre Carbon e formato de string para o banco de dados.
     *
     * @return Attribute Atributo computado para a data de fim
     */
    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value),
            set: fn (Carbon $value) => $value->format('Y-m-d'),
        );
    }

    /**
     * Accessor e mutator para a data de inicio do periodo.
     * Converte entre Carbon e formato de string para o banco de dados.
     *
     * @return Attribute Atributo computado para a data de inicio
     */
    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value),
            set: fn (Carbon $value) => $value->format('Y-m-d'),
        );
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

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'created_at'              => 'datetime',
            'updated_at'              => 'datetime',
            'deleted_at'              => 'datetime',
            'start_date'              => 'date',
            'end_date'                => 'date',
            'transaction_currency_id' => 'int',
            'amount'                  => 'string',
            'native_amount'           => 'string',
            'user_id'                 => 'integer',
            'user_group_id'           => 'integer',
        ];
    }
}
