<?php

/**
 * TransactionType.php
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

use Deprecated;
use FireflyIII\Enums\TransactionTypeEnum;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionType
 *
 * Representa um tipo de transacao no sistema Firefly III.
 * Tipos incluem: Withdrawal (retirada), Deposit (deposito), Transfer (transferencia),
 * Opening balance (saldo inicial), Reconciliation (reconciliacao), etc.
 *
 * @property int                                 $id                  Identificador unico do tipo
 * @property string                              $type                Nome do tipo de transacao
 * @property \Carbon\Carbon                      $created_at          Data de criacao
 * @property \Carbon\Carbon                      $updated_at          Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at          Data de exclusao (soft delete)
 * @property-read \Illuminate\Support\Collection $transactionJournals Diarios de transacao deste tipo
 */
class TransactionType extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    #[Deprecated] /** @deprecated */
    public const string DEPOSIT          = 'Deposit';

    #[Deprecated] /** @deprecated */
    public const string INVALID          = 'Invalid';

    #[Deprecated] /** @deprecated */
    public const string LIABILITY_CREDIT = 'Liability credit';

    #[Deprecated] /** @deprecated */
    public const string OPENING_BALANCE  = 'Opening balance';

    #[Deprecated] /** @deprecated */
    public const string RECONCILIATION   = 'Reconciliation';

    #[Deprecated] /** @deprecated */
    public const string TRANSFER         = 'Transfer';

    #[Deprecated] /** @deprecated */
    public const string WITHDRAWAL       = 'Withdrawal';

    protected $casts
                                         = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    protected $fillable                  = ['type'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $type): self
    {
        if (!auth()->check()) {
            throw new NotFoundHttpException();
        }
        $transactionType = self::where('type', ucfirst($type))->first();
        if (null !== $transactionType) {
            return $transactionType;
        }

        throw new NotFoundHttpException();
    }

    /**
     * Verifica se este tipo e um deposito.
     *
     * @return bool True se for deposito, false caso contrario
     */
    public function isDeposit(): bool
    {
        return TransactionTypeEnum::DEPOSIT->value === $this->type;
    }

    /**
     * Verifica se este tipo e um saldo inicial.
     *
     * @return bool True se for saldo inicial, false caso contrario
     */
    public function isOpeningBalance(): bool
    {
        return TransactionTypeEnum::OPENING_BALANCE->value === $this->type;
    }

    /**
     * Verifica se este tipo e uma transferencia.
     *
     * @return bool True se for transferencia, false caso contrario
     */
    public function isTransfer(): bool
    {
        return TransactionTypeEnum::TRANSFER->value === $this->type;
    }

    /**
     * Verifica se este tipo e uma retirada.
     *
     * @return bool True se for retirada, false caso contrario
     */
    public function isWithdrawal(): bool
    {
        return TransactionTypeEnum::WITHDRAWAL->value === $this->type;
    }

    /**
     * Retorna todos os diarios de transacao deste tipo.
     *
     * @return HasMany Colecao de TransactionJournal relacionados
     */
    public function transactionJournals(): HasMany
    {
        return $this->hasMany(TransactionJournal::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            // 'type' => TransactionTypeEnum::class,
        ];
    }
}
