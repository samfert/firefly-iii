<?php

/**
 * RuleAction.php
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
use FireflyIII\TransactionRules\Expressions\ActionExpression;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * Class RuleAction
 *
 * Representa uma acao a ser executada quando uma regra e acionada.
 * Acoes podem modificar transacoes, como alterar descricao, categoria,
 * tags, contas, e outros atributos.
 *
 * @property int            $id              Identificador unico da acao
 * @property int            $rule_id         ID da regra associada
 * @property string         $action_type     Tipo da acao (ex: set_category, add_tag)
 * @property string         $action_value    Valor da acao
 * @property int            $order           Ordem de execucao
 * @property bool           $active          Se a acao esta ativa
 * @property bool           $stop_processing Se deve parar o processamento apos esta acao
 * @property \Carbon\Carbon $created_at      Data de criacao
 * @property \Carbon\Carbon $updated_at      Data de atualizacao
 * @property-read Rule      $rule            Regra associada
 */
class RuleAction extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['rule_id', 'action_type', 'action_value', 'order', 'active', 'stop_processing'];

    /**
     * Obtem o valor da acao, processando expressoes se o motor de expressoes estiver habilitado.
     *
     * @param array $journal Dados do diario de transacao para avaliacao de expressoes
     *
     * @return string Valor da acao processado
     */
    public function getValue(array $journal): string
    {
        if (false === config('firefly.feature_flags.expression_engine')) {
            Log::debug('Expression engine is disabled, returning action value as string.');

            return (string) $this->action_value;
        }
        if (true === config('firefly.feature_flags.expression_engine') && str_starts_with($this->action_value, '\=')) {
            return substr($this->action_value, 1);
        }
        $expr = new ActionExpression($this->action_value);

        try {
            $result = $expr->evaluate($journal);
        } catch (SyntaxError $e) {
            Log::error(sprintf('Expression engine failed to evaluate expression "%s" with error "%s".', $this->action_value, $e->getMessage()));
            $result = (string) $this->action_value;
        }
        Log::debug(sprintf('Expression engine is enabled, result of expression "%s" is "%s".', $this->action_value, $result));

        return $result;
    }

    /**
     * Retorna a regra associada a esta acao.
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
     * @return Attribute Atributo computado para a ordem de execucao
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
