<?php

/**
 * Bill.php
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Bill
 *
 * Representa uma conta recorrente ou fatura no sistema Firefly III.
 * Faturas sao despesas regulares como aluguel, contas de servicos publicos,
 * assinaturas, etc. que ocorrem em intervalos previsíveis.
 *
 * @property int                                 $id                      Identificador unico da fatura
 * @property int                                 $user_id                 ID do usuario proprietario
 * @property int                                 $user_group_id           ID do grupo de usuarios
 * @property int                                 $transaction_currency_id ID da moeda
 * @property string                              $name                    Nome da fatura
 * @property string|null                         $match                   Padrao de correspondencia para transacoes
 * @property string                              $amount_min              Valor minimo esperado
 * @property string                              $amount_max              Valor maximo esperado
 * @property \Carbon\Carbon                      $date                    Data de vencimento
 * @property string                              $repeat_freq             Frequencia de repeticao
 * @property int                                 $skip                    Numero de periodos a pular
 * @property bool                                $automatch               Se deve corresponder automaticamente
 * @property bool                                $active                  Se a fatura esta ativa
 * @property \Carbon\Carbon|null                 $end_date                Data de fim da fatura
 * @property \Carbon\Carbon|null                 $extension_date          Data de extensao
 * @property \Carbon\Carbon                      $created_at              Data de criacao
 * @property \Carbon\Carbon                      $updated_at              Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at              Data de exclusao (soft delete)
 * @property-read User                           $user                    Usuario proprietario
 * @property-read TransactionCurrency            $transactionCurrency     Moeda da fatura
 * @property-read \Illuminate\Support\Collection $transactionJournals     Transacoes associadas
 * @property-read \Illuminate\Support\Collection $attachments             Anexos da fatura
 * @property-read \Illuminate\Support\Collection $notes                   Notas da fatura
 * @property-read \Illuminate\Support\Collection $objectGroups            Grupos de objetos
 */
class Bill extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable
                      = [
            'name',
            'match',
            'amount_min',
            'user_id',
            'user_group_id',
            'amount_max',
            'date',
            'date_tz',
            'repeat_freq',
            'skip',
            'automatch',
            'active',
            'transaction_currency_id',
            'end_date',
            'extension_date',
            'end_date_tz',
            'extension_date_tz',
            'native_amount_min',
            'native_amount_max',
        ];

    protected $hidden = ['amount_min_encrypted', 'amount_max_encrypted', 'name_encrypted', 'match_encrypted'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $billId = (int) $value;

            /** @var User $user */
            $user   = auth()->user();

            /** @var null|Bill $bill */
            $bill   = $user->bills()->find($billId);
            if (null !== $bill) {
                return $bill;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario desta fatura.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todos os anexos associados a esta fatura.
     *
     * @return MorphMany Colecao polimorfica de Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Retorna todas as notas associadas a esta fatura.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna todos os grupos de objetos aos quais esta fatura pertence.
     *
     * @return MorphToMany Colecao polimorfica de ObjectGroup
     */
    public function objectGroups(): MorphToMany
    {
        return $this->morphToMany(ObjectGroup::class, 'object_groupable');
    }

    /**
     * Define o valor maximo da fatura como string.
     *
     * @param mixed $value Valor maximo a ser definido
     *
     * @return void
     */
    public function setAmountMaxAttribute($value): void
    {
        $this->attributes['amount_max'] = (string) $value;
    }

    /**
     * Define o valor minimo da fatura como string.
     *
     * @param mixed $value Valor minimo a ser definido
     *
     * @return void
     */
    public function setAmountMinAttribute($value): void
    {
        $this->attributes['amount_min'] = (string) $value;
    }

    /**
     * Retorna a moeda associada a esta fatura.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Retorna todas as transacoes associadas a esta fatura.
     *
     * @return HasMany Colecao de TransactionJournal relacionadas
     */
    public function transactionJournals(): HasMany
    {
        return $this->hasMany(TransactionJournal::class);
    }

    /**
     * Accessor para garantir que o valor maximo seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor maximo
     */
    protected function amountMax(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o valor minimo seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor minimo
     */
    protected function amountMin(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
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
     * Accessor para garantir que o numero de periodos a pular seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o numero de periodos a pular
     */
    protected function skip(): Attribute
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
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'deleted_at'        => 'datetime',
            'date'              => SeparateTimezoneCaster::class,
            'end_date'          => SeparateTimezoneCaster::class,
            'extension_date'    => SeparateTimezoneCaster::class,
            'skip'              => 'int',
            'automatch'         => 'boolean',
            'active'            => 'boolean',
            'name_encrypted'    => 'boolean',
            'match_encrypted'   => 'boolean',
            'amount_min'        => 'string',
            'amount_max'        => 'string',
            'native_amount_min' => 'string',
            'native_amount_max' => 'string',
        ];
    }
}
