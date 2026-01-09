<?php

/**
 * TransactionCurrency.php
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
use FireflyIII\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionCurrency
 *
 * Representa uma moeda no sistema Firefly III.
 * Moedas sao usadas para definir valores monetarios em transacoes,
 * contas, orcamentos e outros elementos financeiros.
 *
 * @property int                                 $id               Identificador unico da moeda
 * @property string                              $code             Codigo ISO da moeda (ex: USD, EUR, BRL)
 * @property string                              $name             Nome da moeda
 * @property string                              $symbol           Simbolo da moeda (ex: $, R$)
 * @property int                                 $decimal_places   Numero de casas decimais
 * @property bool                                $enabled          Se a moeda esta habilitada
 * @property \Carbon\Carbon                      $created_at       Data de criacao
 * @property \Carbon\Carbon                      $updated_at       Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at       Data de exclusao (soft delete)
 * @property bool|null                           $userGroupEnabled Se esta habilitada para o grupo do usuario
 * @property bool|null                           $userGroupNative  Se e a moeda nativa do grupo
 * @property-read \Illuminate\Support\Collection $budgetLimits     Limites de orcamento nesta moeda
 * @property-read \Illuminate\Support\Collection $transactionJournals Transacoes nesta moeda
 * @property-read \Illuminate\Support\Collection $transactions     Transacoes
 * @property-read \Illuminate\Support\Collection $userGroups       Grupos de usuarios
 * @property-read \Illuminate\Support\Collection $users            Usuarios
 */
class TransactionCurrency extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    public ?bool $userGroupEnabled = null;
    public ?bool $userGroupNative  = null;

    protected $fillable            = ['name', 'code', 'symbol', 'decimal_places', 'enabled'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $currencyId = (int) $value;
            $currency   = self::find($currencyId);
            if (null !== $currency) {
                $currency->refreshForUser(auth()->user());

                return $currency;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Atualiza as propriedades de habilitacao da moeda para um usuario especifico.
     *
     * @param User $user Usuario para verificar
     *
     * @return void
     */
    public function refreshForUser(User $user): void
    {
        $current                = $user->userGroup->currencies()->where('transaction_currencies.id', $this->id)->first();
        $native                 = app('amount')->getPrimaryCurrencyByUserGroup($user->userGroup);
        $this->userGroupNative  = $native->id === $this->id;
        $this->userGroupEnabled = null !== $current;
    }

    /**
     * Retorna todos os limites de orcamento nesta moeda.
     *
     * @return HasMany Colecao de BudgetLimit relacionados
     */
    public function budgetLimits(): HasMany
    {
        return $this->hasMany(BudgetLimit::class);
    }

    /**
     * Retorna todos os diarios de transacao nesta moeda.
     *
     * @return HasMany Colecao de TransactionJournal relacionados
     */
    public function transactionJournals(): HasMany
    {
        return $this->hasMany(TransactionJournal::class);
    }

    /**
     * Retorna todas as transacoes nesta moeda.
     *
     * @return HasMany Colecao de Transaction relacionadas
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Retorna todos os grupos de usuarios que usam esta moeda.
     *
     * @return BelongsToMany Colecao de UserGroup relacionados
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class)->withTimestamps()->withPivot('group_default');
    }

    /**
     * Retorna todos os usuarios que usam esta moeda.
     *
     * @return BelongsToMany Colecao de User relacionados
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('user_default');
    }

    /**
     * Accessor para garantir que o numero de casas decimais seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o numero de casas decimais
     */
    protected function decimalPlaces(): Attribute
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
            'decimal_places' => 'int',
            'enabled'        => 'bool',
        ];
    }
}
