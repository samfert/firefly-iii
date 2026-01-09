<?php

/*
 * InvitedUser.php
 * Copyright (c) 2022 james@firefly-iii.org
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class InvitedUser
 *
 * Representa um convite enviado para um novo usuario se juntar ao sistema.
 * Armazena informacoes sobre o convite, incluindo codigo de convite,
 * data de expiracao e status de resgate.
 *
 * @property int                 $id            Identificador unico do convite
 * @property int                 $user_id       ID do usuario que enviou o convite
 * @property int                 $user_group_id ID do grupo de usuarios
 * @property string              $email         Email do usuario convidado
 * @property string              $invite_code   Codigo unico do convite
 * @property \Carbon\Carbon      $expires       Data de expiracao do convite
 * @property bool                $redeemed      Se o convite foi resgatado
 * @property \Carbon\Carbon      $created_at    Data de criacao
 * @property \Carbon\Carbon      $updated_at    Data de atualizacao
 * @property-read User           $user          Usuario que enviou o convite
 */
class InvitedUser extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    protected $fillable = ['user_group_id', 'user_id', 'email', 'invite_code', 'expires', 'expires_tz', 'redeemed'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $attemptId = (int) $value;

            /** @var null|InvitedUser $attempt */
            $attempt   = self::find($attemptId);
            if (null !== $attempt) {
                return $attempt;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario que enviou este convite.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'expires'       => SeparateTimezoneCaster::class,
            'redeemed'      => 'boolean',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
