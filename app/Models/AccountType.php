<?php

/**
 * AccountType.php
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
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class AccountType
 *
 * Define os diferentes tipos de contas suportados pelo Firefly III.
 * Tipos incluem: conta corrente (Asset), despesa (Expense), receita (Revenue),
 * cartao de credito (Credit Card), emprestimo (Loan), hipoteca (Mortgage), etc.
 *
 * @property int                                 $id         Identificador unico do tipo de conta
 * @property string                              $type       Nome do tipo de conta
 * @property \Carbon\Carbon                      $created_at Data de criacao
 * @property \Carbon\Carbon                      $updated_at Data de atualizacao
 * @property-read \Illuminate\Support\Collection $accounts   Contas deste tipo
 */
class AccountType extends Model
{
    use ReturnsIntegerIdTrait;

    #[Deprecated] /** @deprecated */
    public const string ASSET            = 'Asset account';

    #[Deprecated] /** @deprecated */
    public const string BENEFICIARY      = 'Beneficiary account';

    #[Deprecated] /** @deprecated */
    public const string CASH             = 'Cash account';

    #[Deprecated] /** @deprecated */
    public const string CREDITCARD       = 'Credit card';

    #[Deprecated] /** @deprecated */
    public const string DEBT             = 'Debt';

    #[Deprecated] /** @deprecated */
    public const string DEFAULT          = 'Default account';

    #[Deprecated] /** @deprecated */
    public const string EXPENSE          = 'Expense account';

    #[Deprecated] /** @deprecated */
    public const string IMPORT           = 'Import account';

    #[Deprecated] /** @deprecated */
    public const string INITIAL_BALANCE  = 'Initial balance account';

    #[Deprecated] /** @deprecated */
    public const string LIABILITY_CREDIT = 'Liability credit account';

    #[Deprecated] /** @deprecated */
    public const string LOAN             = 'Loan';

    #[Deprecated] /** @deprecated */
    public const string MORTGAGE         = 'Mortgage';

    #[Deprecated] /** @deprecated */
    public const string RECONCILIATION   = 'Reconciliation account';

    #[Deprecated] /** @deprecated */
    public const string REVENUE          = 'Revenue account';

    protected $casts
                                         = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

    protected $fillable                  = ['type'];

    /**
     * Retorna todas as contas associadas a este tipo de conta.
     *
     * @return HasMany Colecao de Account relacionadas a este tipo
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            // 'type' => AccountTypeEnum::class,
        ];
    }
}
