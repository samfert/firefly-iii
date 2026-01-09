<?php

/*
 * Webhook.php
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

use FireflyIII\Enums\WebhookDelivery;
use FireflyIII\Enums\WebhookResponse;
use FireflyIII\Enums\WebhookTrigger;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Webhook
 *
 * Representa um webhook no sistema Firefly III.
 * Webhooks permitem notificar sistemas externos quando eventos
 * ocorrem, como criacao ou atualizacao de transacoes.
 *
 * @property int                                 $id              Identificador unico do webhook
 * @property int                                 $user_id         ID do usuario proprietario
 * @property int                                 $user_group_id   ID do grupo de usuarios
 * @property string                              $title           Titulo do webhook
 * @property string                              $url             URL de destino do webhook
 * @property string                              $secret          Segredo para assinatura
 * @property bool                                $active          Se o webhook esta ativo
 * @property int                                 $trigger         Tipo de gatilho
 * @property int                                 $response        Tipo de resposta
 * @property int                                 $delivery        Metodo de entrega
 * @property \Carbon\Carbon                      $created_at      Data de criacao
 * @property \Carbon\Carbon                      $updated_at      Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at      Data de exclusao (soft delete)
 * @property-read User                           $user            Usuario proprietario
 * @property-read \Illuminate\Support\Collection $webhookMessages Mensagens do webhook
 */
class Webhook extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $casts
                        = [
            'active'        => 'boolean',
            'trigger'       => 'integer',
            'response'      => 'integer',
            'delivery'      => 'integer',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    protected $fillable = ['active', 'trigger', 'response', 'delivery', 'user_id', 'user_group_id', 'url', 'title', 'secret'];

    /**
     * Retorna todos os metodos de entrega disponiveis.
     *
     * @return array<int, string> Array de metodos de entrega indexados por valor
     */
    public static function getDeliveries(): array
    {
        $array = [];
        $set   = WebhookDelivery::cases();
        foreach ($set as $item) {
            $array[$item->value] = $item->name;
        }

        return $array;
    }

    /**
     * Retorna os metodos de entrega formatados para validacao.
     *
     * @return array<string|int, int> Array de metodos de entrega para validacao
     */
    public static function getDeliveriesForValidation(): array
    {
        $array = [];
        $set   = WebhookDelivery::cases();
        foreach ($set as $item) {
            $array[$item->name]  = $item->value;
            $array[$item->value] = $item->value;
        }

        return $array;
    }

    /**
     * Retorna todos os tipos de resposta disponiveis.
     *
     * @return array<int, string> Array de tipos de resposta indexados por valor
     */
    public static function getResponses(): array
    {
        $array = [];
        $set   = WebhookResponse::cases();
        foreach ($set as $item) {
            $array[$item->value] = $item->name;
        }

        return $array;
    }

    /**
     * Retorna os tipos de resposta formatados para validacao.
     *
     * @return array<string|int, int> Array de tipos de resposta para validacao
     */
    public static function getResponsesForValidation(): array
    {
        $array = [];
        $set   = WebhookResponse::cases();
        foreach ($set as $item) {
            $array[$item->name]  = $item->value;
            $array[$item->value] = $item->value;
        }

        return $array;
    }

    /**
     * Retorna todos os tipos de gatilho disponiveis.
     *
     * @return array<int, string> Array de tipos de gatilho indexados por valor
     */
    public static function getTriggers(): array
    {
        $array = [];
        $set   = WebhookTrigger::cases();
        foreach ($set as $item) {
            $array[$item->value] = $item->name;
        }

        return $array;
    }

    /**
     * Retorna os tipos de gatilho formatados para validacao.
     *
     * @return array<string|int, int> Array de tipos de gatilho para validacao
     */
    public static function getTriggersForValidation(): array
    {
        $array = [];
        $set   = WebhookTrigger::cases();
        foreach ($set as $item) {
            $array[$item->name]  = $item->value;
            $array[$item->value] = $item->value;
        }

        return $array;
    }

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $webhookId = (int) $value;

            /** @var User $user */
            $user      = auth()->user();

            /** @var null|Webhook $webhook */
            $webhook   = $user->webhooks()->find($webhookId);
            if (null !== $webhook) {
                return $webhook;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario deste webhook.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todas as mensagens deste webhook.
     *
     * @return HasMany Colecao de WebhookMessage relacionadas
     */
    public function webhookMessages(): HasMany
    {
        return $this->hasMany(WebhookMessage::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            //            'delivery' => WebhookDelivery::class,
            //            'response' => WebhookResponse::class,
            //            'trigger'  => WebhookTrigger::class,
        ];
    }
}
