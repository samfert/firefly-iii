<?php

/**
 * Attachment.php
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
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Attachment
 *
 * Representa um arquivo anexado a entidades do sistema como transacoes, contas, etc.
 * Anexos podem ser comprovantes, recibos, documentos ou qualquer arquivo relevante
 * para o registro financeiro.
 *
 * @property int                                 $id              Identificador unico do anexo
 * @property int                                 $user_id         ID do usuario proprietario
 * @property int                                 $user_group_id   ID do grupo de usuarios
 * @property int                                 $attachable_id   ID da entidade anexada
 * @property string                              $attachable_type Tipo da entidade anexada
 * @property string                              $md5             Hash MD5 do arquivo
 * @property string                              $filename        Nome original do arquivo
 * @property string                              $mime            Tipo MIME do arquivo
 * @property string|null                         $title           Titulo do anexo
 * @property string|null                         $description     Descricao do anexo
 * @property int                                 $size            Tamanho do arquivo em bytes
 * @property bool                                $uploaded        Indica se o upload foi concluido
 * @property \Carbon\Carbon                      $created_at      Data de criacao
 * @property \Carbon\Carbon                      $updated_at      Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at      Data de exclusao (soft delete)
 * @property-read User                           $user            Usuario proprietario
 * @property-read Model                          $attachable      Entidade anexada
 * @property-read \Illuminate\Support\Collection $notes           Notas do anexo
 */
class Attachment extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['attachable_id', 'attachable_type', 'user_id', 'user_group_id', 'md5', 'filename', 'mime', 'title', 'description', 'size', 'uploaded'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $attachmentId = (int) $value;

            /** @var User $user */
            $user         = auth()->user();

            /** @var null|Attachment $attachment */
            $attachment   = $user->attachments()->find($attachmentId);
            if (null !== $attachment) {
                return $attachment;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario do anexo.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna a entidade a qual este anexo esta vinculado.
     * Pode ser uma transacao, conta, fatura ou qualquer outra entidade anexavel.
     *
     * @return MorphTo Relacionamento polimorfico com a entidade anexada
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Retorna o nome do arquivo esperado para este anexo no sistema de arquivos.
     * O nome segue o padrao 'at-{id}.data' para armazenamento interno.
     *
     * @return string Nome do arquivo no formato 'at-{id}.data'
     */
    public function fileName(): string
    {
        return sprintf('at-%s.data', (string) $this->id);
    }

    /**
     * Retorna todas as notas associadas a este anexo.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Accessor para garantir que o ID da entidade anexada seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da entidade anexada
     */
    protected function attachableId(): Attribute
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
            'uploaded'      => 'boolean',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
