<?php


/*
 * AccountBalance.php
 * Copyright (c) 2025 james@firefly-iii.org.
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
 * along with this program.  If not, see https://www.gnu.org/licenses/.
 */

declare(strict_types=1);

namespace FireflyIII\Models;

use FireflyIII\Casts\SeparateTimezoneCaster;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AccountBalance
 *
 * Representa o saldo de uma conta em um determinado momento no tempo.
 * Armazena snapshots do saldo da conta para diferentes moedas e datas,
 * permitindo rastrear o historico de saldos ao longo do tempo.
 *
 * @property int                      $id                      Identificador unico do registro de saldo
 * @property int                      $account_id              ID da conta associada
 * @property string                   $title                   Titulo ou descricao do saldo
 * @property int                      $transaction_currency_id ID da moeda do saldo
 * @property string                   $balance                 Valor do saldo
 * @property \Carbon\Carbon           $date                    Data do saldo
 * @property string                   $date_tz                 Fuso horario da data
 * @property-read Account             $account                 Conta associada
 * @property-read TransactionCurrency $transactionCurrency     Moeda do saldo
 */
class AccountBalance extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'title', 'transaction_currency_id', 'balance', 'date', 'date_tz'];

    /**
     * Retorna a conta associada a este registro de saldo.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Retorna a moeda associada a este saldo.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'date'    => SeparateTimezoneCaster::class,
            'balance' => 'string',
        ];
    }
}
