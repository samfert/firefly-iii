<?php

/**
 * PiggyBank.php
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PiggyBank
 *
 * Representa um cofrinho (meta de poupanca) no sistema Firefly III.
 * Cofrinhos permitem ao usuario definir metas de economia com valores
 * alvo e datas limite, acompanhando o progresso ao longo do tempo.
 *
 * @property int                                 $id                      Identificador unico do cofrinho
 * @property int                                 $account_id              ID da conta associada
 * @property int                                 $transaction_currency_id ID da moeda
 * @property string                              $name                    Nome do cofrinho
 * @property string                              $target_amount           Valor alvo a ser economizado
 * @property string|null                         $native_target_amount    Valor alvo na moeda nativa
 * @property \Carbon\Carbon|null                 $start_date              Data de inicio
 * @property \Carbon\Carbon|null                 $target_date             Data alvo para atingir a meta
 * @property int                                 $order                   Ordem de exibicao
 * @property bool                                $active                  Se o cofrinho esta ativo
 * @property \Carbon\Carbon                      $created_at              Data de criacao
 * @property \Carbon\Carbon                      $updated_at              Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at              Data de exclusao (soft delete)
 * @property-read Account                        $account                 Conta associada
 * @property-read \Illuminate\Support\Collection $accounts                Contas associadas
 * @property-read TransactionCurrency            $transactionCurrency     Moeda do cofrinho
 * @property-read \Illuminate\Support\Collection $piggyBankEvents         Eventos do cofrinho
 * @property-read \Illuminate\Support\Collection $piggyBankRepetitions    Repeticoes do cofrinho
 * @property-read \Illuminate\Support\Collection $attachments             Anexos
 * @property-read \Illuminate\Support\Collection $notes                   Notas
 * @property-read \Illuminate\Support\Collection $objectGroups            Grupos de objetos
 */
class PiggyBank extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable = ['name', 'order', 'target_amount', 'start_date', 'start_date_tz', 'target_date', 'target_date_tz', 'active', 'transaction_currency_id', 'native_target_amount'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $piggyBankId = (int) $value;
            $piggyBank   = self::where('piggy_banks.id', $piggyBankId)
                ->leftJoin('account_piggy_bank', 'account_piggy_bank.piggy_bank_id', '=', 'piggy_banks.id')
                ->leftJoin('accounts', 'accounts.id', '=', 'account_piggy_bank.account_id')
                ->where('accounts.user_id', auth()->user()->id)->first(['piggy_banks.*'])
            ;
            if (null !== $piggyBank) {
                return $piggyBank;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna a conta principal associada a este cofrinho.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Retorna todas as contas associadas a este cofrinho com valores atuais.
     *
     * @return BelongsToMany Colecao de Account com dados de pivot
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withPivot(['current_amount', 'native_current_amount']);
    }

    /**
     * Retorna todos os anexos associados a este cofrinho.
     *
     * @return MorphMany Colecao polimorfica de Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Retorna todas as notas associadas a este cofrinho.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna todos os grupos de objetos aos quais este cofrinho pertence.
     *
     * @return MorphToMany Colecao polimorfica de ObjectGroup
     */
    public function objectGroups(): MorphToMany
    {
        return $this->morphToMany(ObjectGroup::class, 'object_groupable');
    }

    /**
     * Retorna todos os eventos deste cofrinho.
     *
     * @return HasMany Colecao de PiggyBankEvent relacionados
     */
    public function piggyBankEvents(): HasMany
    {
        return $this->hasMany(PiggyBankEvent::class);
    }

    /**
     * Retorna todas as repeticoes deste cofrinho.
     *
     * @return HasMany Colecao de PiggyBankRepetition relacionadas
     */
    public function piggyBankRepetitions(): HasMany
    {
        return $this->hasMany(PiggyBankRepetition::class);
    }

    /**
     * Define o valor alvo do cofrinho como string.
     *
     * @param mixed $value Valor alvo a ser definido
     *
     * @return void
     */
    public function setTargetAmountAttribute($value): void
    {
        $this->attributes['target_amount'] = (string) $value;
    }

    /**
     * Retorna a moeda associada a este cofrinho.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
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
     * Accessor para garantir que a ordem seja retornada como inteiro.
     *
     * @return Attribute Atributo computado para a ordem de exibicao
     */
    protected function order(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o valor alvo seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor alvo
     */
    protected function targetAmount(): Attribute
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
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
            'deleted_at'           => 'datetime',
            'start_date'           => 'date',
            'target_date'          => 'date',
            'order'                => 'int',
            'active'               => 'boolean',
            'encrypted'            => 'boolean',
            'target_amount'        => 'string',
            'native_target_amount' => 'string',
        ];
    }
}
