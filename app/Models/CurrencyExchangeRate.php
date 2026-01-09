<?php

/**
 * CurrencyExchangeRate.php
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
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CurrencyExchangeRate
 *
 * Armazena taxas de cambio entre moedas para uma data especifica.
 * Permite ao sistema converter valores entre diferentes moedas
 * usando taxas historicas ou definidas pelo usuario.
 *
 * @property int                      $id               Identificador unico da taxa de cambio
 * @property int                      $user_id          ID do usuario proprietario
 * @property int                      $from_currency_id ID da moeda de origem
 * @property int                      $to_currency_id   ID da moeda de destino
 * @property \Carbon\Carbon           $date             Data da taxa de cambio
 * @property string                   $rate             Taxa de cambio
 * @property string|null              $user_rate        Taxa definida pelo usuario
 * @property \Carbon\Carbon           $created_at       Data de criacao
 * @property \Carbon\Carbon           $updated_at       Data de atualizacao
 * @property-read User                $user             Usuario proprietario
 * @property-read TransactionCurrency $fromCurrency     Moeda de origem
 * @property-read TransactionCurrency $toCurrency       Moeda de destino
 */
class CurrencyExchangeRate extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;
    protected $fillable = ['user_id', 'from_currency_id', 'to_currency_id', 'date', 'date_tz', 'rate'];

    /**
     * Retorna a moeda de origem desta taxa de cambio.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class, 'from_currency_id');
    }

    /**
     * Retorna a moeda de destino desta taxa de cambio.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class, 'to_currency_id');
    }

    /**
     * Retorna o usuario proprietario desta taxa de cambio.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor para garantir que o ID da moeda de origem seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da moeda de origem
     */
    protected function fromCurrencyId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que a taxa de cambio seja retornada como string.
     *
     * @return Attribute Atributo computado para a taxa de cambio
     */
    protected function rate(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o ID da moeda de destino seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da moeda de destino
     */
    protected function toCurrencyId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que a taxa definida pelo usuario seja retornada como string.
     *
     * @return Attribute Atributo computado para a taxa do usuario
     */
    protected function userRate(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
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
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
            'user_id'          => 'integer',
            'user_group_id'    => 'integer',
            'from_currency_id' => 'integer',
            'to_currency_id'   => 'integer',
            'date'             => SeparateTimezoneCaster::class,
            'rate'             => 'string',
            'user_rate'        => 'string',
        ];
    }
}
