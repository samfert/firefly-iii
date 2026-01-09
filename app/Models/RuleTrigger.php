<?php

/**
 * RuleTrigger.php
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

/**
 * Class RuleTrigger
 *
 * Representa um gatilho (trigger) que ativa uma regra.
 * Gatilhos definem condicoes que devem ser atendidas para
 * que as acoes da regra sejam executadas.
 *
 * @property int            $id              Identificador unico do gatilho
 * @property int            $rule_id         ID da regra associada
 * @property string         $trigger_type    Tipo do gatilho (ex: description_contains)
 * @property string         $trigger_value   Valor do gatilho
 * @property int            $order           Ordem de avaliacao
 * @property bool           $active          Se o gatilho esta ativo
 * @property bool           $stop_processing Se deve parar o processamento apos este gatilho
 * @property \Carbon\Carbon $created_at      Data de criacao
 * @property \Carbon\Carbon $updated_at      Data de atualizacao
 * @property-read Rule      $rule            Regra associada
 */
class RuleTrigger extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['rule_id', 'trigger_type', 'trigger_value', 'order', 'active', 'stop_processing'];

    /**
     * Retorna a regra associada a este gatilho.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Rule
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    /**
     * Accessor para garantir que a ordem seja retornada como inteiro.
     *
     * @return Attribute Atributo computado para a ordem de avaliacao
     */
    protected function order(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID da regra seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da regra
     */
    protected function ruleId(): Attribute
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
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
            'active'          => 'boolean',
            'order'           => 'int',
            'stop_processing' => 'boolean',
        ];
    }
}
