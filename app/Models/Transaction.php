<?php

/**
 * Transaction.php
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

use Illuminate\Database\Eloquent\Attributes\Scope;
use Carbon\Carbon;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Transaction
 *
 * Representa uma transacao financeira individual no sistema Firefly III.
 * Cada transacao pertence a um diario de transacao (TransactionJournal) e
 * esta associada a uma conta. Transacoes podem ter valores em moeda estrangeira.
 *
 * @property int                                 $id                      Identificador unico da transacao
 * @property int                                 $account_id              ID da conta associada
 * @property int                                 $transaction_journal_id  ID do diario de transacao
 * @property int                                 $transaction_currency_id ID da moeda principal
 * @property int|null                            $foreign_currency_id     ID da moeda estrangeira
 * @property string                              $description             Descricao da transacao
 * @property string                              $amount                  Valor da transacao
 * @property string|null                         $foreign_amount          Valor em moeda estrangeira
 * @property string                              $native_amount           Valor na moeda nativa
 * @property int                                 $identifier              Identificador sequencial
 * @property bool                                $reconciled              Se a transacao foi reconciliada
 * @property \Carbon\Carbon                      $created_at              Data de criacao
 * @property \Carbon\Carbon                      $updated_at              Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at              Data de exclusao (soft delete)
 * @property-read Account                        $account                 Conta associada
 * @property-read TransactionJournal             $transactionJournal      Diario de transacao
 * @property-read TransactionCurrency            $transactionCurrency     Moeda principal
 * @property-read TransactionCurrency|null       $foreignCurrency         Moeda estrangeira
 * @property-read \Illuminate\Support\Collection $budgets                 Orcamentos associados
 * @property-read \Illuminate\Support\Collection $categories              Categorias associadas
 */
class Transaction extends Model
{
    use HasFactory;
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable
                      = [
            'account_id',
            'transaction_journal_id',
            'description',
            'amount',
            'native_amount',
            'native_foreign_amount',
            'identifier',
            'transaction_currency_id',
            'foreign_currency_id',
            'foreign_amount',
            'reconciled',
        ];

    protected $hidden = ['encrypted'];

    /**
     * Get the account this object belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the budget(s) this object belongs to.
     */
    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class);
    }

    /**
     * Get the category(ies) this object belongs to.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the currency this object belongs to.
     */
    public function foreignCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class, 'foreign_currency_id');
    }

    /**
     * Check for transactions AFTER a specified date.
     */
    #[Scope]
    protected function after(Builder $query, Carbon $date): void
    {
        if (!self::isJoined($query, 'transaction_journals')) {
            $query->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id');
        }
        $query->where('transaction_journals.date', '>=', $date->format('Y-m-d 00:00:00'));
    }

    /**
     * Check if a table is joined.
     */
    public static function isJoined(Builder $query, string $table): bool
    {
        $joins = $query->getQuery()->joins;

        foreach ($joins as $join) {
            if ($join->table === $table) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for transactions BEFORE the specified date.
     */
    #[Scope]
    protected function before(Builder $query, Carbon $date): void
    {
        if (!self::isJoined($query, 'transaction_journals')) {
            $query->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id');
        }
        $query->where('transaction_journals.date', '<=', $date->format('Y-m-d 23:59:59'));
    }

    #[Scope]
    protected function transactionTypes(Builder $query, array $types): void
    {
        if (!self::isJoined($query, 'transaction_journals')) {
            $query->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id');
        }

        if (!self::isJoined($query, 'transaction_types')) {
            $query->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id');
        }
        $query->whereIn('transaction_types.type', $types);
    }

    /**
     * Define o valor da transacao como string.
     *
     * @param mixed $value Valor a ser definido
     *
     * @return void
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = (string) $value;
    }

    /**
     * Retorna a moeda principal desta transacao.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Retorna o diario de transacao ao qual esta transacao pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionJournal
     */
    public function transactionJournal(): BelongsTo
    {
        return $this->belongsTo(TransactionJournal::class);
    }

    /**
     * Accessor para garantir que o ID da conta seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da conta
     */
    protected function accountId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
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
     * Accessor para verificar se o saldo precisa ser recalculado.
     *
     * @return Attribute Atributo computado para o estado de saldo sujo
     */
    protected function balanceDirty(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => 1 === (int) $value,
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
     * Accessor para garantir que o ID do diario de transacao seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do diario
     */
    protected function transactionJournalId(): Attribute
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
            'created_at'            => 'datetime',
            'updated_at'            => 'datetime',
            'deleted_at'            => 'datetime',
            'identifier'            => 'int',
            'encrypted'             => 'boolean',
            'bill_name_encrypted'   => 'boolean',
            'reconciled'            => 'boolean',
            'balance_dirty'         => 'boolean',
            'balance_before'        => 'string',
            'balance_after'         => 'string',
            'date'                  => 'datetime',
            'amount'                => 'string',
            'foreign_amount'        => 'string',
            'native_amount'         => 'string',
            'native_foreign_amount' => 'string',
        ];
    }
}
