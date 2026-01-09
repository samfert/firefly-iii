<?php

/*
 * UserGroup.php
 * Copyright (c) 2021 james@firefly-iii.org
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

use FireflyIII\Enums\UserRoleEnum;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserGroup
 *
 * Representa um grupo de usuarios no sistema Firefly III.
 * Grupos de usuarios permitem compartilhar dados financeiros entre
 * multiplos usuarios, como familias ou organizacoes.
 *
 * @property int                                 $id                    Identificador unico do grupo
 * @property string                              $title                 Titulo do grupo
 * @property \Carbon\Carbon                      $created_at            Data de criacao
 * @property \Carbon\Carbon                      $updated_at            Data de atualizacao
 * @property-read \Illuminate\Support\Collection $accounts              Contas do grupo
 * @property-read \Illuminate\Support\Collection $attachments           Anexos do grupo
 * @property-read \Illuminate\Support\Collection $availableBudgets      Orcamentos disponiveis
 * @property-read \Illuminate\Support\Collection $bills                 Faturas do grupo
 * @property-read \Illuminate\Support\Collection $budgets               Orcamentos do grupo
 * @property-read \Illuminate\Support\Collection $categories            Categorias do grupo
 * @property-read \Illuminate\Support\Collection $currencies            Moedas do grupo
 * @property-read \Illuminate\Support\Collection $currencyExchangeRates Taxas de cambio
 * @property-read \Illuminate\Support\Collection $groupMemberships      Membros do grupo
 * @property-read \Illuminate\Support\Collection $objectGroups          Grupos de objetos
 * @property-read \Illuminate\Support\Collection $piggyBanks            Cofrinhos
 * @property-read \Illuminate\Support\Collection $recurrences           Recorrencias
 * @property-read \Illuminate\Support\Collection $ruleGroups            Grupos de regras
 * @property-read \Illuminate\Support\Collection $rules                 Regras
 * @property-read \Illuminate\Support\Collection $tags                  Tags
 * @property-read \Illuminate\Support\Collection $transactionGroups     Grupos de transacoes
 * @property-read \Illuminate\Support\Collection $transactionJournals   Diarios de transacao
 * @property-read \Illuminate\Support\Collection $webhooks              Webhooks
 */
class UserGroup extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['title'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $userGroupId = (int) $value;

            /** @var User $user */
            $user        = auth()->user();

            /** @var null|UserGroup $userGroup */
            $userGroup   = self::find($userGroupId);
            if (null === $userGroup) {
                throw new NotFoundHttpException();
            }
            // need at least ready only to be aware of the user group's existence,
            // but owner/full role (in the group) or global owner role may overrule this.
            $access      = $user->hasRoleInGroupOrOwner($userGroup, UserRoleEnum::READ_ONLY) || $user->hasRole('owner');
            if ($access) {
                return $userGroup;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna todas as contas deste grupo de usuarios.
     *
     * @return HasMany Colecao de Account relacionadas
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Retorna todos os anexos deste grupo de usuarios.
     *
     * @return HasMany Colecao de Attachment relacionados
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Retorna todos os orcamentos disponiveis deste grupo de usuarios.
     *
     * @return HasMany Colecao de AvailableBudget relacionados
     */
    public function availableBudgets(): HasMany
    {
        return $this->hasMany(AvailableBudget::class);
    }

    /**
     * Retorna todas as faturas deste grupo de usuarios.
     *
     * @return HasMany Colecao de Bill relacionadas
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Retorna todos os orcamentos deste grupo de usuarios.
     *
     * @return HasMany Colecao de Budget relacionados
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Retorna todas as categorias deste grupo de usuarios.
     *
     * @return HasMany Colecao de Category relacionadas
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Retorna todas as moedas habilitadas para este grupo de usuarios.
     *
     * @return BelongsToMany Colecao de TransactionCurrency relacionadas
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(TransactionCurrency::class)->withTimestamps()->withPivot('group_default');
    }

    /**
     * Retorna todas as taxas de cambio deste grupo de usuarios.
     *
     * @return HasMany Colecao de CurrencyExchangeRate relacionadas
     */
    public function currencyExchangeRates(): HasMany
    {
        return $this->hasMany(CurrencyExchangeRate::class);
    }

    /**
     * Retorna todas as associacoes de membros deste grupo.
     *
     * @return HasMany Colecao de GroupMembership relacionadas
     */
    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    /**
     * Retorna todos os grupos de objetos deste grupo de usuarios.
     *
     * @return HasMany Colecao de ObjectGroup relacionados
     */
    public function objectGroups(): HasMany
    {
        return $this->hasMany(ObjectGroup::class);
    }

    /**
     * Retorna todos os cofrinhos deste grupo de usuarios atraves das contas.
     *
     * @return HasManyThrough Colecao de PiggyBank relacionados
     */
    public function piggyBanks(): HasManyThrough
    {
        return $this->hasManyThrough(PiggyBank::class, Account::class);
    }

    /**
     * Retorna todas as recorrencias deste grupo de usuarios.
     *
     * @return HasMany Colecao de Recurrence relacionadas
     */
    public function recurrences(): HasMany
    {
        return $this->hasMany(Recurrence::class);
    }

    /**
     * Retorna todos os grupos de regras deste grupo de usuarios.
     *
     * @return HasMany Colecao de RuleGroup relacionados
     */
    public function ruleGroups(): HasMany
    {
        return $this->hasMany(RuleGroup::class);
    }

    /**
     * Retorna todas as regras deste grupo de usuarios.
     *
     * @return HasMany Colecao de Rule relacionadas
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /**
     * Retorna todas as tags deste grupo de usuarios.
     *
     * @return HasMany Colecao de Tag relacionadas
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Retorna todos os grupos de transacoes deste grupo de usuarios.
     *
     * @return HasMany Colecao de TransactionGroup relacionados
     */
    public function transactionGroups(): HasMany
    {
        return $this->hasMany(TransactionGroup::class);
    }

    /**
     * Retorna todos os diarios de transacao deste grupo de usuarios.
     *
     * @return HasMany Colecao de TransactionJournal relacionados
     */
    public function transactionJournals(): HasMany
    {
        return $this->hasMany(TransactionJournal::class);
    }

    /**
     * Retorna todos os webhooks deste grupo de usuarios.
     *
     * @return HasMany Colecao de Webhook relacionados
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }
}
