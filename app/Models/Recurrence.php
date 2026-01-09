<?php

/**
 * Recurrence.php
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
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Recurrence
 *
 * Representa uma transacao recorrente no sistema Firefly III.
 * Transacoes recorrentes sao criadas automaticamente em intervalos
 * definidos, como pagamentos mensais, salarios, etc.
 *
 * @property int                                 $id                  Identificador unico da recorrencia
 * @property int                                 $user_id             ID do usuario proprietario
 * @property int                                 $user_group_id       ID do grupo de usuarios
 * @property int                                 $transaction_type_id ID do tipo de transacao
 * @property string                              $title               Titulo da recorrencia
 * @property string|null                         $description         Descricao da recorrencia
 * @property \Carbon\Carbon                      $first_date          Data da primeira ocorrencia
 * @property \Carbon\Carbon|null                 $repeat_until        Data limite para repeticoes
 * @property \Carbon\Carbon|null                 $latest_date         Data da ultima ocorrencia
 * @property int                                 $repetitions         Numero de repeticoes
 * @property bool                                $apply_rules         Se deve aplicar regras
 * @property bool                                $active              Se a recorrencia esta ativa
 * @property \Carbon\Carbon                      $created_at          Data de criacao
 * @property \Carbon\Carbon                      $updated_at          Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at          Data de exclusao (soft delete)
 * @property-read User                           $user                Usuario proprietario
 * @property-read TransactionType                $transactionType     Tipo de transacao
 * @property-read \Illuminate\Support\Collection $recurrenceMeta      Metadados da recorrencia
 * @property-read \Illuminate\Support\Collection $recurrenceRepetitions Repeticoes
 * @property-read \Illuminate\Support\Collection $recurrenceTransactions Transacoes
 * @property-read \Illuminate\Support\Collection $attachments         Anexos
 * @property-read \Illuminate\Support\Collection $notes               Notas
 */
class Recurrence extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable
                     = ['user_id', 'user_group_id', 'transaction_type_id', 'title', 'description', 'first_date', 'first_date_tz', 'repeat_until', 'repeat_until_tz', 'latest_date', 'latest_date_tz', 'repetitions', 'apply_rules', 'active'];

    protected $table = 'recurrences';

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $recurrenceId = (int) $value;

            /** @var User $user */
            $user         = auth()->user();

            /** @var null|Recurrence $recurrence */
            $recurrence   = $user->recurrences()->find($recurrenceId);
            if (null !== $recurrence) {
                return $recurrence;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario desta recorrencia.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todos os anexos associados a esta recorrencia.
     *
     * @return MorphMany Colecao polimorfica de Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Retorna todas as notas associadas a esta recorrencia.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna todos os metadados desta recorrencia.
     *
     * @return HasMany Colecao de RecurrenceMeta relacionados
     */
    public function recurrenceMeta(): HasMany
    {
        return $this->hasMany(RecurrenceMeta::class);
    }

    /**
     * Retorna todas as repeticoes desta recorrencia.
     *
     * @return HasMany Colecao de RecurrenceRepetition relacionadas
     */
    public function recurrenceRepetitions(): HasMany
    {
        return $this->hasMany(RecurrenceRepetition::class);
    }

    /**
     * Retorna todas as transacoes desta recorrencia.
     *
     * @return HasMany Colecao de RecurrenceTransaction relacionadas
     */
    public function recurrenceTransactions(): HasMany
    {
        return $this->hasMany(RecurrenceTransaction::class);
    }

    /**
     * Retorna a moeda associada a esta recorrencia.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Retorna o tipo de transacao desta recorrencia.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionType
     */
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }

    /**
     * Accessor para garantir que o ID do tipo de transacao seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do tipo de transacao
     */
    protected function transactionTypeId(): Attribute
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
            'deleted_at'    => 'datetime',
            'title'         => 'string',
            'id'            => 'int',
            'description'   => 'string',
            'first_date'    => SeparateTimezoneCaster::class,
            'repeat_until'  => SeparateTimezoneCaster::class,
            'latest_date'   => SeparateTimezoneCaster::class,
            'repetitions'   => 'int',
            'active'        => 'bool',
            'apply_rules'   => 'bool',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
