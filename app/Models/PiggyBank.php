<?php

/**
 * PiggyBank.php
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PiggyBank
 *
 * Represents a savings goal that helps users save money for specific purposes.
 * Piggy banks are virtual containers that track progress toward a target amount,
 * allowing users to allocate portions of their account balance for specific goals
 * like vacations, emergency funds, or large purchases.
 *
 * Piggy banks are linked to one or more asset accounts and track the current
 * saved amount separately from the actual account balance. This allows users
 * to mentally allocate funds without moving money between accounts.
 *
 * Key features:
 * - Target amount and optional target date for goal tracking
 * - Progress tracking with events for deposits and withdrawals
 * - Can be linked to multiple accounts
 * - Supports repetitions for recurring savings goals
 * - Can be organized into object groups
 * - Supports attachments and notes
 *
 * @property int $id Primary key identifier
 * @property string $name Display name of the piggy bank
 * @property int $order Display order for sorting
 * @property string $target_amount Target savings amount
 * @property \Carbon\Carbon|null $start_date When saving started
 * @property \Carbon\Carbon|null $target_date Target date to reach the goal
 * @property bool $active Whether the piggy bank is currently active
 * @property int $transaction_currency_id Foreign key to the currency
 * @property string|null $native_target_amount Target amount in native currency
 * @property \Carbon\Carbon $created_at Timestamp of creation
 * @property \Carbon\Carbon $updated_at Timestamp of last update
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 */
class PiggyBank extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable = ['name', 'order', 'target_amount', 'start_date', 'start_date_tz', 'target_date', 'target_date_tz', 'active', 'transaction_currency_id', 'native_target_amount'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $piggyBankId = (int) $value;
            $piggyBank   = self::where('piggy_banks.id', $piggyBankId)
                ->leftJoin('account_piggy_bank', 'account_piggy_bank.piggy_bank_id', '=', 'piggy_banks.id')
                ->leftJoin('accounts', 'accounts.id', '=', 'account_piggy_bank.account_id')
                ->where('accounts.user_id', auth()->user()->id)->first(['piggy_banks.*'])
            ;
            if (null !== $piggyBank) {
                return $piggyBank;
            }
        }

        throw new NotFoundHttpException();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withPivot(['current_amount', 'native_current_amount']);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all the piggy bank's notes.
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Get all the tags for the post.
     */
    public function objectGroups(): MorphToMany
    {
        return $this->morphToMany(ObjectGroup::class, 'object_groupable');
    }

    public function piggyBankEvents(): HasMany
    {
        return $this->hasMany(PiggyBankEvent::class);
    }

    public function piggyBankRepetitions(): HasMany
    {
        return $this->hasMany(PiggyBankRepetition::class);
    }

    /**
     * @param mixed $value
     */
    public function setTargetAmountAttribute($value): void
    {
        $this->attributes['target_amount'] = (string) $value;
    }

    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    protected function accountId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    protected function order(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Get the max amount
     */
    protected function targetAmount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    protected function casts(): array
    {
        return [
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
            'deleted_at'           => 'datetime',
            'start_date'           => 'date',
            'target_date'          => 'date',
            'order'                => 'int',
            'active'               => 'boolean',
            'encrypted'            => 'boolean',
            'target_amount'        => 'string',
            'native_target_amount' => 'string',
        ];
    }
}
