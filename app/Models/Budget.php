<?php

/**
 * Budget.php
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Budget
 *
 * Representa um orcamento no sistema Firefly III. Orcamentos permitem ao usuario
 * definir limites de gastos para categorias especificas de despesas, ajudando
 * no controle financeiro e planejamento de gastos.
 *
 * @property int                                 $id              Identificador unico do orcamento
 * @property int                                 $user_id         ID do usuario proprietario
 * @property int                                 $user_group_id   ID do grupo de usuarios
 * @property string                              $name            Nome do orcamento
 * @property bool                                $active          Se o orcamento esta ativo
 * @property int                                 $order           Ordem de exibicao
 * @property \Carbon\Carbon                      $created_at      Data de criacao
 * @property \Carbon\Carbon                      $updated_at      Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at      Data de exclusao (soft delete)
 * @property-read User                           $user            Usuario proprietario
 * @property-read \Illuminate\Support\Collection $autoBudgets     Auto-orcamentos associados
 * @property-read \Illuminate\Support\Collection $budgetlimits    Limites de orcamento
 * @property-read \Illuminate\Support\Collection $transactionJournals Transacoes associadas
 * @property-read \Illuminate\Support\Collection $transactions    Transacoes
 * @property-read \Illuminate\Support\Collection $attachments     Anexos
 * @property-read \Illuminate\Support\Collection $notes           Notas
 */
class Budget extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['user_id', 'user_group_id', 'name', 'active', 'order', 'user_group_id'];

    protected $hidden   = ['encrypted'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $budgetId = (int) $value;

            /** @var User $user */
            $user     = auth()->user();

            /** @var null|Budget $budget */
            $budget   = $user->budgets()->find($budgetId);
            if (null !== $budget) {
                return $budget;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario deste orcamento.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todos os anexos associados a este orcamento.
     *
     * @return MorphMany Colecao polimorfica de Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Retorna todos os auto-orcamentos associados a este orcamento.
     *
     * @return HasMany Colecao de AutoBudget relacionados
     */
    public function autoBudgets(): HasMany
    {
        return $this->hasMany(AutoBudget::class);
    }

    /**
     * Retorna todos os limites de orcamento associados.
     *
     * @return HasMany Colecao de BudgetLimit relacionados
     */
    public function budgetlimits(): HasMany
    {
        return $this->hasMany(BudgetLimit::class);
    }

    /**
     * Retorna todas as notas associadas a este orcamento.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna todos os diarios de transacao associados a este orcamento.
     *
     * @return BelongsToMany Colecao de TransactionJournal relacionados
     */
    public function transactionJournals(): BelongsToMany
    {
        return $this->belongsToMany(TransactionJournal::class, 'budget_transaction_journal', 'budget_id');
    }

    /**
     * Retorna todas as transacoes associadas a este orcamento.
     *
     * @return BelongsToMany Colecao de Transaction relacionadas
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'budget_transaction', 'budget_id');
    }

    /**
     * Accessor para garantir que a ordem seja retornada como inteiro.
     *
     * @return Attribute Atributo computado para a ordem de exibicao
     */
    protected function order(): Attribute
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
            'active'        => 'boolean',
            'encrypted'     => 'boolean',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
