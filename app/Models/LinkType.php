<?php

/**
 * LinkType.php
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class LinkType
 *
 * Define tipos de links que podem conectar transacoes entre si.
 * Permite criar relacionamentos semanticos entre transacoes,
 * como "pago por", "reembolsado por", "relacionado a", etc.
 *
 * @property int                                 $id         Identificador unico do tipo de link
 * @property string                              $name       Nome do tipo de link
 * @property string                              $inward     Descricao do link na direcao de entrada
 * @property string                              $outward    Descricao do link na direcao de saida
 * @property bool                                $editable   Se o tipo de link pode ser editado
 * @property \Carbon\Carbon                      $created_at Data de criacao
 * @property \Carbon\Carbon                      $updated_at Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at Data de exclusao (soft delete)
 * @property-read \Illuminate\Support\Collection $transactionJournalLinks Links de transacao deste tipo
 */
class LinkType extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable = ['name', 'inward', 'outward', 'editable'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $linkTypeId = (int) $value;
            $linkType   = self::find($linkTypeId);
            if (null !== $linkType) {
                return $linkType;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna todos os links de transacao deste tipo.
     *
     * @return HasMany Colecao de TransactionJournalLink relacionados
     */
    public function transactionJournalLinks(): HasMany
    {
        return $this->hasMany(TransactionJournalLink::class);
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
            'deleted_at' => 'datetime',
            'editable'   => 'boolean',
        ];
    }
}
