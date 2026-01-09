<?php

/**
 * TransactionJournalLink.php
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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionJournalLink
 *
 * Representa um link entre dois diarios de transacao no sistema Firefly III.
 * Links permitem conectar transacoes relacionadas, como pagamentos e reembolsos,
 * ou transacoes que se referem uma a outra.
 *
 * @property int                                 $id             Identificador unico do link
 * @property int                                 $link_type_id   ID do tipo de link
 * @property int                                 $source_id      ID do diario de origem
 * @property int                                 $destination_id ID do diario de destino
 * @property \Carbon\Carbon                      $created_at     Data de criacao
 * @property \Carbon\Carbon                      $updated_at     Data de atualizacao
 * @property-read LinkType                       $linkType       Tipo de link
 * @property-read TransactionJournal             $source         Diario de origem
 * @property-read TransactionJournal             $destination    Diario de destino
 * @property-read \Illuminate\Support\Collection $notes          Notas
 */
class TransactionJournalLink extends Model
{
    use ReturnsIntegerIdTrait;

    protected $table = 'journal_links';

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $linkId = (int) $value;
            $link   = self::where('journal_links.id', $linkId)
                ->leftJoin('transaction_journals as t_a', 't_a.id', '=', 'source_id')
                ->leftJoin('transaction_journals as t_b', 't_b.id', '=', 'destination_id')
                ->where('t_a.user_id', auth()->user()->id)
                ->where('t_b.user_id', auth()->user()->id)
                ->first(['journal_links.*'])
            ;
            if (null !== $link) {
                return $link;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o diario de transacao de destino deste link.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionJournal
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(TransactionJournal::class, 'destination_id');
    }

    /**
     * Retorna o tipo de link deste relacionamento.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo LinkType
     */
    public function linkType(): BelongsTo
    {
        return $this->belongsTo(LinkType::class);
    }

    /**
     * Retorna todas as notas associadas a este link.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna o diario de transacao de origem deste link.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionJournal
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(TransactionJournal::class, 'source_id');
    }

    /**
     * Accessor para garantir que o ID do destino seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do destino
     */
    protected function destinationId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do tipo de link seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do tipo de link
     */
    protected function linkTypeId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID da origem seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da origem
     */
    protected function sourceId(): Attribute
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
