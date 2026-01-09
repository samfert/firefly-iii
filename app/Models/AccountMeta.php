<?php

/**
 * AccountMeta.php
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

use Illuminate\Database\Eloquent\Casts\Attribute;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function Safe\json_decode;
use function Safe\json_encode;

/**
 * Class AccountMeta
 *
 * Armazena metadados adicionais para contas financeiras.
 * Permite armazenar informacoes extras como numero da conta, BIC, codigo do banco,
 * e outros dados especificos que nao fazem parte do modelo principal de conta.
 *
 * @property int            $id         Identificador unico do metadado
 * @property int            $account_id ID da conta associada
 * @property string         $name       Nome/chave do metadado
 * @property string         $data       Valor do metadado (armazenado como JSON)
 * @property \Carbon\Carbon $created_at Data de criacao
 * @property \Carbon\Carbon $updated_at Data de atualizacao
 * @property-read Account   $account    Conta associada
 */
class AccountMeta extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['account_id', 'name', 'data'];
    protected $table    = 'account_meta';

    /**
     * Retorna a conta associada a este metadado.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Accessor e mutator para o campo data.
     * Decodifica JSON ao ler e codifica ao salvar.
     *
     * @return Attribute Atributo computado para manipulacao de dados JSON
     */
    protected function data(): Attribute
    {
        return Attribute::make(get: fn (mixed $value) => (string) json_decode((string) $value, true), set: fn (mixed $value) => ['data' => json_encode($value)]);
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
        ];
    }
}
