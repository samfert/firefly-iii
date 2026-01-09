<?php

/**
 * Tag.php
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Tag
 *
 * Representa uma tag para classificar transacoes no sistema Firefly III.
 * Tags permitem ao usuario adicionar marcadores personalizados as transacoes
 * para facilitar a organizacao e busca.
 *
 * @property int                                 $id              Identificador unico da tag
 * @property int                                 $user_id         ID do usuario proprietario
 * @property int                                 $user_group_id   ID do grupo de usuarios
 * @property string                              $tag             Nome da tag
 * @property string|null                         $description     Descricao da tag
 * @property string|null                         $tag_mode        Modo da tag
 * @property \Carbon\Carbon|null                 $date            Data associada a tag
 * @property \Carbon\Carbon                      $created_at      Data de criacao
 * @property \Carbon\Carbon                      $updated_at      Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at      Data de exclusao (soft delete)
 * @property-read User                           $user            Usuario proprietario
 * @property-read \Illuminate\Support\Collection $transactionJournals Transacoes com esta tag
 * @property-read \Illuminate\Support\Collection $attachments     Anexos
 * @property-read \Illuminate\Support\Collection $locations       Localizacoes
 */
class Tag extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['user_id', 'user_group_id', 'tag', 'date', 'date_tz', 'description', 'tag_mode'];

    protected $hidden   = ['zoomLevel', 'latitude', 'longitude'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $tagId = (int) $value;

            /** @var User $user */
            $user  = auth()->user();

            /** @var null|Tag $tag */
            $tag   = $user->tags()->find($tagId);
            if (null !== $tag) {
                return $tag;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario desta tag.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todos os anexos associados a esta tag.
     *
     * @return MorphMany Colecao polimorfica de Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Retorna todas as localizacoes associadas a esta tag.
     *
     * @return MorphMany Colecao polimorfica de Location
     */
    public function locations(): MorphMany
    {
        return $this->morphMany(Location::class, 'locatable');
    }

    /**
     * Retorna todos os diarios de transacao com esta tag.
     *
     * @return BelongsToMany Colecao de TransactionJournal relacionados
     */
    public function transactionJournals(): BelongsToMany
    {
        return $this->belongsToMany(TransactionJournal::class);
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
            'zoomLevel'     => 'int',
            'latitude'      => 'float',
            'longitude'     => 'float',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
