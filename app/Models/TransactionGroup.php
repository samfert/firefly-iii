<?php

/**
 * TransactionGroup.php
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionGroup
 *
 * Representa um grupo de transacoes no sistema Firefly III.
 * Grupos de transacoes permitem agrupar multiplas transacoes relacionadas,
 * como transacoes divididas (split transactions).
 *
 * @property int                                 $id              Identificador unico do grupo
 * @property int                                 $user_id         ID do usuario proprietario
 * @property int                                 $user_group_id   ID do grupo de usuarios
 * @property string|null                         $title           Titulo do grupo de transacoes
 * @property \Carbon\Carbon                      $created_at      Data de criacao
 * @property \Carbon\Carbon                      $updated_at      Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at      Data de exclusao (soft delete)
 * @property-read User                           $user            Usuario proprietario
 * @property-read UserGroup                      $userGroup       Grupo de usuarios
 * @property-read \Illuminate\Support\Collection $transactionJournals Diarios de transacao neste grupo
 */
class TransactionGroup extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['user_id', 'user_group_id', 'title'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        app('log')->debug(sprintf('Now in %s("%s")', __METHOD__, $value));
        if (auth()->check()) {
            $groupId = (int) $value;

            /** @var User $user */
            $user    = auth()->user();
            app('log')->debug(sprintf('User authenticated as %s', $user->email));

            /** @var null|TransactionGroup $group */
            $group   = $user->transactionGroups()
                ->with(['transactionJournals', 'transactionJournals.transactions'])
                ->where('transaction_groups.id', $groupId)->first(['transaction_groups.*'])
            ;
            if (null !== $group) {
                app('log')->debug(sprintf('Found group #%d.', $group->id));

                return $group;
            }
        }
        app('log')->debug('Found no group.');

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario deste grupo de transacoes.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todos os diarios de transacao neste grupo.
     *
     * @return HasMany Colecao de TransactionJournal relacionados
     */
    public function transactionJournals(): HasMany
    {
        return $this->hasMany(TransactionJournal::class);
    }

    /**
     * Retorna o grupo de usuarios ao qual este grupo de transacoes pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo UserGroup
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'id'            => 'integer',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
            'deleted_at'    => 'datetime',
            'title'         => 'string',
            'date'          => 'datetime',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
