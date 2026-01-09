<?php

/*
 * WebhookMessage.php
 * Copyright (c) 2021 james@firefly-iii.org
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
use FireflyIII\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class WebhookMessage
 *
 * Representa uma mensagem de webhook no sistema Firefly III.
 * Mensagens contem os dados a serem enviados para o endpoint do webhook
 * e rastreiam o status de envio.
 *
 * @property int                                 $id              Identificador unico da mensagem
 * @property int                                 $webhook_id      ID do webhook associado
 * @property string                              $uuid            UUID unico da mensagem
 * @property array                               $message         Conteudo da mensagem (JSON)
 * @property bool                                $sent            Se a mensagem foi enviada
 * @property bool                                $errored         Se houve erro no envio
 * @property array|null                          $logs            Logs de envio (JSON)
 * @property \Carbon\Carbon                      $created_at      Data de criacao
 * @property \Carbon\Carbon                      $updated_at      Data de atualizacao
 * @property-read Webhook                        $webhook         Webhook associado
 * @property-read \Illuminate\Support\Collection $webhookAttempts Tentativas de envio
 */
class WebhookMessage extends Model
{
    use ReturnsIntegerIdTrait;

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $messageId = (int) $value;

            /** @var User $user */
            $user      = auth()->user();

            /** @var null|WebhookMessage $message */
            $message   = self::find($messageId);
            if (null !== $message && $message->webhook->user_id === $user->id) {
                return $message;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o webhook associado a esta mensagem.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Webhook
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    /**
     * Retorna todas as tentativas de envio desta mensagem.
     *
     * @return HasMany Colecao de WebhookAttempt relacionadas
     */
    public function webhookAttempts(): HasMany
    {
        return $this->hasMany(WebhookAttempt::class);
    }

    /**
     * Accessor para garantir que o status de envio seja retornado como booleano.
     *
     * @return Attribute Atributo computado para o status de envio
     */
    protected function sent(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (bool) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do webhook seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do webhook
     */
    protected function webhookId(): Attribute
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
            'sent'    => 'boolean',
            'errored' => 'boolean',
            'uuid'    => 'string',
            'message' => 'json',
            'logs'    => 'json',
        ];
    }
}
