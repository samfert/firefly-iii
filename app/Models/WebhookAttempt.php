<?php

/*
 * WebhookAttempt.php
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
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class WebhookAttempt
 *
 * Representa uma tentativa de envio de webhook no sistema Firefly III.
 * Cada tentativa registra o resultado do envio de uma mensagem de webhook,
 * incluindo codigo de resposta, logs e status de sucesso.
 *
 * @property int                 $id                 Identificador unico da tentativa
 * @property int                 $webhook_message_id ID da mensagem de webhook
 * @property int|null            $status_code        Codigo de status HTTP da resposta
 * @property string|null         $logs               Logs da tentativa
 * @property \Carbon\Carbon      $created_at         Data de criacao
 * @property \Carbon\Carbon      $updated_at         Data de atualizacao
 * @property \Carbon\Carbon|null $deleted_at         Data de exclusao (soft delete)
 * @property-read WebhookMessage $webhookMessage     Mensagem de webhook associada
 */
class WebhookAttempt extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $attemptId = (int) $value;

            /** @var User $user */
            $user      = auth()->user();

            /** @var null|WebhookAttempt $attempt */
            $attempt   = self::find($attemptId);
            if (null !== $attempt && $attempt->webhookMessage->webhook->user_id === $user->id) {
                return $attempt;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna a mensagem de webhook associada a esta tentativa.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo WebhookMessage
     */
    public function webhookMessage(): BelongsTo
    {
        return $this->belongsTo(WebhookMessage::class);
    }

    /**
     * Accessor para garantir que o ID da mensagem seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da mensagem de webhook
     */
    protected function webhookMessageId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }
}
