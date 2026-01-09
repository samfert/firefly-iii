<?php

/**
 * TransactionJournal.php
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
use FireflyIII\Casts\SeparateTimezoneCaster;
use FireflyIII\Enums\TransactionTypeEnum;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionJournal
 *
 * Representa um diario de transacao no sistema Firefly III.
 * O diario de transacao e o registro principal de uma transacao financeira,
 * contendo informacoes como data, descricao, tipo e relacionamentos.
 *
 * @property int                                 $id                      Identificador unico do diario
 * @property int                                 $user_id                 ID do usuario proprietario
 * @property int                                 $user_group_id           ID do grupo de usuarios
 * @property int                                 $transaction_type_id     ID do tipo de transacao
 * @property int                                 $transaction_currency_id ID da moeda
 * @property int|null                            $transaction_group_id    ID do grupo de transacoes
 * @property int|null                            $bill_id                 ID da fatura associada
 * @property string                              $description             Descricao da transacao
 * @property \Carbon\Carbon                      $date                    Data da transacao
 * @property int                                 $order                   Ordem de exibicao
 * @property int                                 $tag_count               Numero de tags
 * @property bool                                $completed               Se a transacao esta completa
 * @property \Carbon\Carbon                      $created_at              Data de criacao
 * @property \Carbon\Carbon                      $updated_at              Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at              Data de exclusao (soft delete)
 * @property-read User                           $user                    Usuario proprietario
 * @property-read UserGroup                      $userGroup               Grupo de usuarios
 * @property-read TransactionType                $transactionType         Tipo de transacao
 * @property-read TransactionCurrency            $transactionCurrency     Moeda
 * @property-read TransactionGroup|null          $transactionGroup        Grupo de transacoes
 * @property-read Bill|null                      $bill                    Fatura associada
 * @property-read \Illuminate\Support\Collection $transactions            Transacoes
 * @property-read \Illuminate\Support\Collection $categories              Categorias
 * @property-read \Illuminate\Support\Collection $budgets                 Orcamentos
 * @property-read \Illuminate\Support\Collection $tags                    Tags
 * @property-read \Illuminate\Support\Collection $attachments             Anexos
 * @property-read \Illuminate\Support\Collection $notes                   Notas
 * @property-read \Illuminate\Support\Collection $locations               Localizacoes
 * @property-read \Illuminate\Support\Collection $piggyBankEvents         Eventos de cofrinho
 * @property-read \Illuminate\Support\Collection $transactionJournalMeta  Metadados
 * @property-read \Illuminate\Support\Collection $sourceJournalLinks      Links de origem
 * @property-read \Illuminate\Support\Collection $destJournalLinks        Links de destino
 * @property-read \Illuminate\Support\Collection $auditLogEntries         Entradas de log de auditoria
 *
 * @method        EloquentBuilder|static before()
 * @method        EloquentBuilder|static after()
 * @method static EloquentBuilder|static query()
 */
class TransactionJournal extends Model
{
    use HasFactory;
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable
                      = [
            'user_id',
            'user_group_id',
            'transaction_type_id',
            'bill_id',
            'tag_count',
            'transaction_currency_id',
            'description',
            'completed',
            'order',
            'date',
            'date_tz',
        ];

    protected $hidden = ['encrypted'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $journalId = (int) $value;

            /** @var User $user */
            $user      = auth()->user();

            /** @var null|TransactionJournal $journal */
            $journal   = $user->transactionJournals()->where('transaction_journals.id', $journalId)->first(['transaction_journals.*']);
            if (null !== $journal) {
                return $journal;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario deste diario de transacao.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todos os anexos associados a este diario de transacao.
     *
     * @return MorphMany Colecao polimorfica de Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Retorna todas as entradas de log de auditoria deste diario.
     *
     * @return MorphMany Colecao polimorfica de AuditLogEntry
     */
    public function auditLogEntries(): MorphMany
    {
        return $this->morphMany(AuditLogEntry::class, 'auditable');
    }

    /**
     * Retorna a fatura associada a este diario de transacao.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Bill
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Retorna todos os orcamentos associados a este diario de transacao.
     *
     * @return BelongsToMany Colecao de Budget relacionados
     */
    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class);
    }

    /**
     * Retorna todas as categorias associadas a este diario de transacao.
     *
     * @return BelongsToMany Colecao de Category relacionadas
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Retorna todos os links onde este diario e o destino.
     *
     * @return HasMany Colecao de TransactionJournalLink relacionados
     */
    public function destJournalLinks(): HasMany
    {
        return $this->hasMany(TransactionJournalLink::class, 'destination_id');
    }

    /**
     * Verifica se esta transacao e uma transferencia.
     *
     * @return bool True se for uma transferencia, false caso contrario
     */
    public function isTransfer(): bool
    {
        if (null !== $this->transaction_type_type) {
            return TransactionTypeEnum::TRANSFER->value === $this->transaction_type_type;
        }

        return $this->transactionType->isTransfer();
    }

    /**
     * Retorna todas as localizacoes associadas a este diario de transacao.
     *
     * @return MorphMany Colecao polimorfica de Location
     */
    public function locations(): MorphMany
    {
        return $this->morphMany(Location::class, 'locatable');
    }

    /**
     * Retorna todas as notas associadas a este diario de transacao.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna todos os eventos de cofrinho associados a este diario.
     *
     * @return HasMany Colecao de PiggyBankEvent relacionados
     */
    public function piggyBankEvents(): HasMany
    {
        return $this->hasMany(PiggyBankEvent::class);
    }

    /**
     * Scope para filtrar transacoes apos uma data especifica.
     *
     * @param EloquentBuilder $query Query builder
     * @param Carbon          $date  Data de referencia
     *
     * @return EloquentBuilder Query builder modificado
     */
    public function scopeAfter(EloquentBuilder $query, Carbon $date): EloquentBuilder
    {
        return $query->where('transaction_journals.date', '>=', $date->format('Y-m-d H:i:s'));
    }

    /**
     * Scope para filtrar transacoes antes de uma data especifica.
     *
     * @param EloquentBuilder $query Query builder
     * @param Carbon          $date  Data de referencia
     *
     * @return EloquentBuilder Query builder modificado
     */
    public function scopeBefore(EloquentBuilder $query, Carbon $date): EloquentBuilder
    {
        return $query->where('transaction_journals.date', '<=', $date->format('Y-m-d H:i:s'));
    }

    /**
     * Scope para filtrar por tipos de transacao.
     *
     * @param EloquentBuilder $query Query builder
     * @param array           $types Tipos de transacao para filtrar
     *
     * @return void
     */
    #[Scope]
    protected function transactionTypes(EloquentBuilder $query, array $types): void
    {
        if (!self::isJoined($query, 'transaction_types')) {
            $query->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id');
        }
        if (0 !== count($types)) {
            $query->whereIn('transaction_types.type', $types);
        }
    }

    /**
     * Verifica se uma tabela ja esta unida na query.
     *
     * @param EloquentBuilder $query Query builder
     * @param string          $table Nome da tabela
     *
     * @return bool True se a tabela ja esta unida, false caso contrario
     */
    public static function isJoined(EloquentBuilder $query, string $table): bool
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
     * Retorna todos os links onde este diario e a origem.
     *
     * @return HasMany Colecao de TransactionJournalLink relacionados
     */
    public function sourceJournalLinks(): HasMany
    {
        return $this->hasMany(TransactionJournalLink::class, 'source_id');
    }

    /**
     * Retorna todas as tags associadas a este diario de transacao.
     *
     * @return BelongsToMany Colecao de Tag relacionadas
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Retorna a moeda deste diario de transacao.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Retorna o grupo de transacoes ao qual este diario pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionGroup
     */
    public function transactionGroup(): BelongsTo
    {
        return $this->belongsTo(TransactionGroup::class);
    }

    /**
     * Retorna todos os metadados deste diario de transacao.
     *
     * @return HasMany Colecao de TransactionJournalMeta relacionados
     */
    public function transactionJournalMeta(): HasMany
    {
        return $this->hasMany(TransactionJournalMeta::class);
    }

    /**
     * Retorna o tipo de transacao deste diario.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionType
     */
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }

    /**
     * Retorna todas as transacoes deste diario.
     *
     * @return HasMany Colecao de Transaction relacionadas
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Retorna o grupo de usuarios ao qual este diario pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo UserGroup
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
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
            'date'          => SeparateTimezoneCaster::class,
            'interest_date' => 'date',
            'book_date'     => 'date',
            'process_date'  => 'date',
            'order'         => 'int',
            'tag_count'     => 'int',
            'encrypted'     => 'boolean',
            'completed'     => 'boolean',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
