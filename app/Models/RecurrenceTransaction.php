<?php

/**
 * RecurrenceTransaction.php
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

use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RecurrenceTransaction
 *
 * Define os detalhes de uma transacao dentro de uma recorrencia.
 * Especifica contas de origem e destino, valores, moedas e
 * outras informacoes necessarias para criar a transacao.
 *
 * @property int                                 $id                      Identificador unico
 * @property int                                 $recurrence_id           ID da recorrencia
 * @property int                                 $transaction_currency_id ID da moeda principal
 * @property int|null                            $foreign_currency_id     ID da moeda estrangeira
 * @property int                                 $source_id               ID da conta de origem
 * @property int                                 $destination_id          ID da conta de destino
 * @property string                              $amount                  Valor da transacao
 * @property string|null                         $foreign_amount          Valor em moeda estrangeira
 * @property string                              $description             Descricao da transacao
 * @property \Carbon\Carbon                      $created_at              Data de criacao
 * @property \Carbon\Carbon                      $updated_at              Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at              Data de exclusao (soft delete)
 * @property-read Recurrence                     $recurrence              Recorrencia associada
 * @property-read Account                        $sourceAccount           Conta de origem
 * @property-read Account                        $destinationAccount      Conta de destino
 * @property-read TransactionCurrency            $transactionCurrency     Moeda principal
 * @property-read TransactionCurrency|null       $foreignCurrency         Moeda estrangeira
 * @property-read \Illuminate\Support\Collection $recurrenceTransactionMeta Metadados
 */
class RecurrenceTransaction extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable
                     = [
            'recurrence_id',
            'transaction_currency_id',
            'foreign_currency_id',
            'source_id',
            'destination_id',
            'amount',
            'foreign_amount',
            'description',
        ];

    protected $table = 'recurrences_transactions';

    /**
     * Retorna a conta de destino desta transacao recorrente.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Account
     */
    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_id');
    }

    /**
     * Retorna a moeda estrangeira desta transacao recorrente.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function foreignCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Retorna a recorrencia a qual esta transacao pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Recurrence
     */
    public function recurrence(): BelongsTo
    {
        return $this->belongsTo(Recurrence::class);
    }

    /**
     * Retorna todos os metadados desta transacao recorrente.
     *
     * @return HasMany Colecao de RecurrenceTransactionMeta relacionados
     */
    public function recurrenceTransactionMeta(): HasMany
    {
        return $this->hasMany(RecurrenceTransactionMeta::class, 'rt_id');
    }

    /**
     * Retorna a conta de origem desta transacao recorrente.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Account
     */
    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'source_id');
    }

    /**
     * Retorna a moeda principal desta transacao recorrente.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Retorna o tipo de transacao desta transacao recorrente.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionType
     */
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }

    /**
     * Accessor para garantir que o valor seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor da transacao
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o ID da conta de destino seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da conta de destino
     */
    protected function destinationId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o valor em moeda estrangeira seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor em moeda estrangeira
     */
    protected function foreignAmount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o ID da recorrencia seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da recorrencia
     */
    protected function recurrenceId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID da conta de origem seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da conta de origem
     */
    protected function sourceId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
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
     * Accessor para garantir que o ID do usuario seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do usuario
     */
    protected function userId(): Attribute
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
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
            'deleted_at'     => 'datetime',
            'amount'         => 'string',
            'foreign_amount' => 'string',
            'description'    => 'string',
        ];
    }
}
