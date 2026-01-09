<?php

/**
 * TransactionJournalMeta.php
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
use Illuminate\Database\Eloquent\SoftDeletes;

use function Safe\json_decode;
use function Safe\json_encode;

/**
 * Class TransactionJournalMeta
 *
 * Armazena metadados adicionais para diarios de transacao.
 * Permite armazenar informacoes extras como datas de juros,
 * datas de processamento, referencias externas, etc.
 *
 * @property int                    $id                     Identificador unico do metadado
 * @property int                    $transaction_journal_id ID do diario de transacao
 * @property string                 $name                   Nome/chave do metadado
 * @property mixed                  $data                   Dados do metadado (armazenados como JSON)
 * @property string                 $hash                   Hash dos dados para verificacao
 * @property \Carbon\Carbon         $created_at             Data de criacao
 * @property \Carbon\Carbon         $updated_at             Data de atualizacao
 * @property \Carbon\Carbon|null    $deleted_at             Data de exclusao (soft delete)
 * @property-read TransactionJournal $transactionJournal    Diario de transacao associado
 */
class TransactionJournalMeta extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable = ['transaction_journal_id', 'name', 'data', 'hash'];

    protected $table    = 'journal_meta';

    /**
     * Accessor e mutator para os dados do metadado.
     * Decodifica JSON ao ler e codifica ao salvar, gerando um hash.
     *
     * @return Attribute Atributo computado para os dados
     */
    protected function data(): Attribute
    {
        return Attribute::make(get: fn ($value) => json_decode((string) $value, false), set: function ($value) {
            $data = json_encode($value);

            return ['data' => $data, 'hash' => hash('sha256', (string) $data)];
        });
    }

    /**
     * Retorna o diario de transacao associado a este metadado.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionJournal
     */
    public function transactionJournal(): BelongsTo
    {
        return $this->belongsTo(TransactionJournal::class);
    }

    /**
     * Accessor para garantir que o ID do diario seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do diario de transacao
     */
    protected function transactionJournalId(): Attribute
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
