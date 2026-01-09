<?php

/**
 * TransactionJournal.php
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

use Illuminate\Database\Eloquent\Attributes\Scope;
use Carbon\Carbon;
use FireflyIII\Casts\SeparateTimezoneCaster;
use FireflyIII\Enums\TransactionTypeEnum;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionJournal
 *
 * Represents a complete financial transaction in the double-entry bookkeeping system.
 * A transaction journal is the parent record that groups together the individual
 * Transaction records (debits and credits) that make up a complete financial event.
 *
 * In Firefly III's double-entry system, every transaction journal contains at least
 * two Transaction records: one representing money leaving an account (negative amount)
 * and one representing money entering another account (positive amount). The sum of
 * all transactions in a journal always equals zero.
 *
 * Transaction journals can be of different types:
 * - Withdrawal: Money leaving an asset account to an expense account
 * - Deposit: Money entering an asset account from a revenue account
 * - Transfer: Money moving between two asset accounts
 * - Opening Balance: Initial balance when setting up an account
 * - Reconciliation: Adjustments made during account reconciliation
 *
 * Key features:
 * - Groups related transactions together
 * - Supports multiple currencies through foreign amounts
 * - Can be linked to bills for recurring expense tracking
 * - Supports budgets, categories, and tags for organization
 * - Can be linked to other journals for split transactions
 * - Maintains audit log for change tracking
 * - Timezone-aware date handling
 *
 * @property int $id Primary key identifier
 * @property int $user_id Foreign key to the owning user
 * @property int $user_group_id Foreign key to the user group
 * @property int $transaction_type_id Foreign key to the transaction type
 * @property int|null $bill_id Foreign key to associated bill
 * @property int $tag_count Number of tags attached
 * @property int $transaction_currency_id Foreign key to the currency
 * @property string $description Description of the transaction
 * @property bool $completed Whether the transaction is completed
 * @property int $order Display order for sorting
 * @property \Carbon\Carbon $date Date of the transaction
 * @property \Carbon\Carbon $created_at Timestamp of creation
 * @property \Carbon\Carbon $updated_at Timestamp of last update
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @method        EloquentBuilder|static before()
 * @method        EloquentBuilder|static after()
 * @method static EloquentBuilder|static query()
 */
class TransactionJournal extends Model
{
    use HasFactory;
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable
                      = [
            'user_id',
            'user_group_id',
            'transaction_type_id',
            'bill_id',
            'tag_count',
            'transaction_currency_id',
            'description',
            'completed',
            'order',
            'date',
            'date_tz',
        ];

    protected $hidden = ['encrypted'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $journalId = (int) $value;

            /** @var User $user */
            $user      = auth()->user();

            /** @var null|TransactionJournal $journal */
            $journal   = $user->transactionJournals()->where('transaction_journals.id', $journalId)->first(['transaction_journals.*']);
            if (null !== $journal) {
                return $journal;
            }
        }

        throw new NotFoundHttpException();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function auditLogEntries(): MorphMany
    {
        return $this->morphMany(AuditLogEntry::class, 'auditable');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function destJournalLinks(): HasMany
    {
        return $this->hasMany(TransactionJournalLink::class, 'destination_id');
    }

    public function isTransfer(): bool
    {
        if (null !== $this->transaction_type_type) {
            return TransactionTypeEnum::TRANSFER->value === $this->transaction_type_type;
        }

        return $this->transactionType->isTransfer();
    }

    public function locations(): MorphMany
    {
        return $this->morphMany(Location::class, 'locatable');
    }

    /**
     * Get all the notes.
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function piggyBankEvents(): HasMany
    {
        return $this->hasMany(PiggyBankEvent::class);
    }

    public function scopeAfter(EloquentBuilder $query, Carbon $date): EloquentBuilder
    {
        return $query->where('transaction_journals.date', '>=', $date->format('Y-m-d H:i:s'));
    }

    public function scopeBefore(EloquentBuilder $query, Carbon $date): EloquentBuilder
    {
        return $query->where('transaction_journals.date', '<=', $date->format('Y-m-d H:i:s'));
    }

    #[Scope]
    protected function transactionTypes(EloquentBuilder $query, array $types): void
    {
        if (!self::isJoined($query, 'transaction_types')) {
            $query->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id');
        }
        if (0 !== count($types)) {
            $query->whereIn('transaction_types.type', $types);
        }
    }

    /**
     * Checks if tables are joined.
     */
    public static function isJoined(EloquentBuilder $query, string $table): bool
    {
        $joins = $query->getQuery()->joins;
        foreach ($joins as $join) {
            if ($join->table === $table) {
                return true;
            }
        }

        return false;
    }

    public function sourceJournalLinks(): HasMany
    {
        return $this->hasMany(TransactionJournalLink::class, 'source_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    public function transactionGroup(): BelongsTo
    {
        return $this->belongsTo(TransactionGroup::class);
    }

    public function transactionJournalMeta(): HasMany
    {
        return $this->hasMany(TransactionJournalMeta::class);
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    protected function order(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    protected function transactionTypeId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    protected function casts(): array
    {
        return [
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
            'deleted_at'    => 'datetime',
            'date'          => SeparateTimezoneCaster::class,
            'interest_date' => 'date',
            'book_date'     => 'date',
            'process_date'  => 'date',
            'order'         => 'int',
            'tag_count'     => 'int',
            'encrypted'     => 'boolean',
            'completed'     => 'boolean',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
        ];
    }
}
