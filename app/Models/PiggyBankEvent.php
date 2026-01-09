<?php

/**
 * PiggyBankEvent.php
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

use FireflyIII\Casts\SeparateTimezoneCaster;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PiggyBankEvent
 *
 * Registra eventos de deposito ou retirada em um cofrinho.
 * Cada evento representa uma alteracao no saldo do cofrinho,
 * permitindo rastrear o historico de contribuicoes.
 *
 * @property int                    $id                     Identificador unico do evento
 * @property int                    $piggy_bank_id          ID do cofrinho associado
 * @property int|null               $transaction_journal_id ID da transacao associada
 * @property \Carbon\Carbon         $date                   Data do evento
 * @property string                 $amount                 Valor do evento (positivo ou negativo)
 * @property string|null            $native_amount          Valor na moeda nativa
 * @property \Carbon\Carbon         $created_at             Data de criacao
 * @property \Carbon\Carbon         $updated_at             Data de atualizacao
 * @property-read PiggyBank         $piggyBank              Cofrinho associado
 * @property-read TransactionJournal|null $transactionJournal Transacao associada
 */
class PiggyBankEvent extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['piggy_bank_id', 'transaction_journal_id', 'date', 'date_tz', 'amount', 'native_amount'];

    protected $hidden   = ['amount_encrypted'];

    /**
     * Retorna o cofrinho associado a este evento.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo PiggyBank
     */
    public function piggyBank(): BelongsTo
    {
        return $this->belongsTo(PiggyBank::class);
    }

    /**
     * Define o valor do evento como string.
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
     * Retorna a transacao associada a este evento.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionJournal
     */
    public function transactionJournal(): BelongsTo
    {
        return $this->belongsTo(TransactionJournal::class);
    }

    /**
     * Accessor para garantir que o valor seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor do evento
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do cofrinho seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do cofrinho
     */
    protected function piggyBankId(): Attribute
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
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
            'date'          => SeparateTimezoneCaster::class,
            'amount'        => 'string',
            'native_amount' => 'string',
        ];
    }
}
