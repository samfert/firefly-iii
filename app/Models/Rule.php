<?php

/**
 * Rule.php
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Rule
 *
 * Represents an automation rule that can automatically modify transactions based
 * on configurable triggers and actions. Rules are a powerful feature that allows
 * users to automate repetitive tasks like categorizing transactions, setting budgets,
 * or adding tags based on transaction properties.
 *
 * Each rule consists of:
 * - Triggers: Conditions that must be met for the rule to fire (e.g., description contains "grocery")
 * - Actions: Operations to perform when triggers match (e.g., set category to "Food")
 *
 * Rules can operate in strict mode (all triggers must match) or non-strict mode
 * (any trigger can match). They are organized into rule groups for better management
 * and can be ordered to control execution priority.
 *
 * Key features:
 * - Flexible trigger conditions based on transaction properties
 * - Multiple action types for transaction modification
 * - Strict/non-strict matching modes
 * - Rule groups for organization
 * - Execution order control
 * - Can be enabled/disabled individually
 *
 * @property int $id Primary key identifier
 * @property int $user_id Foreign key to the owning user
 * @property int $user_group_id Foreign key to the user group
 * @property int $rule_group_id Foreign key to the rule group
 * @property int $order Execution order within the group
 * @property bool $active Whether the rule is currently active
 * @property string $title Display title of the rule
 * @property string|null $description Description of what the rule does
 * @property bool $strict Whether all triggers must match (true) or any (false)
 * @property bool $stop_processing Whether to stop processing other rules after this one
 * @property \Carbon\Carbon $created_at Timestamp of creation
 * @property \Carbon\Carbon $updated_at Timestamp of last update
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 */
class Rule extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['rule_group_id', 'order', 'active', 'title', 'description', 'user_id', 'user_group_id', 'strict'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $ruleId = (int) $value;

            /** @var User $user */
            $user   = auth()->user();

            /** @var null|Rule $rule */
            $rule   = $user->rules()->find($ruleId);
            if (null !== $rule) {
                return $rule;
            }
        }

        throw new NotFoundHttpException();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ruleActions(): HasMany
    {
        return $this->hasMany(RuleAction::class);
    }

    public function ruleGroup(): BelongsTo
    {
        return $this->belongsTo(RuleGroup::class);
    }

    public function ruleTriggers(): HasMany
    {
        return $this->hasMany(RuleTrigger::class);
    }

    protected function description(): Attribute
    {
        return Attribute::make(set: fn ($value) => ['description' => e($value)]);
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

    protected function ruleGroupId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    protected function casts(): array
    {
        return [
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
            'deleted_at'      => 'datetime',
            'active'          => 'boolean',
            'order'           => 'int',
            'stop_processing' => 'boolean',
            'id'              => 'int',
            'strict'          => 'boolean',
            'user_id'         => 'integer',
            'user_group_id'   => 'integer',
        ];
    }
}
