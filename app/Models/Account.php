<?php

/**
 * Account.php
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
use FireflyIII\Enums\AccountTypeEnum;
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
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Account
 *
 * Representa uma conta financeira no sistema Firefly III. As contas podem ser de diversos tipos,
 * como contas correntes, poupanca, cartoes de credito, ativos, passivos, entre outros.
 * Esta classe e o modelo central para gerenciar todas as contas do usuario.
 *
 * @property int                                      $id                     Identificador unico da conta
 * @property int                                      $user_id                ID do usuario proprietario
 * @property int                                      $user_group_id          ID do grupo de usuarios
 * @property int                                      $account_type_id        ID do tipo de conta
 * @property string                                   $name                   Nome da conta
 * @property bool                                     $active                 Indica se a conta esta ativa
 * @property string|null                              $virtual_balance        Saldo virtual da conta
 * @property string|null                              $iban                   Numero IBAN da conta
 * @property string|null                              $native_virtual_balance Saldo virtual na moeda nativa
 * @property \Carbon\Carbon                           $created_at             Data de criacao
 * @property \Carbon\Carbon                           $updated_at             Data de atualizacao
 * @property \Carbon\Carbon|null                      $deleted_at             Data de exclusao (soft delete)
 * @property-read AccountType                         $accountType            Tipo da conta
 * @property-read User                                $user                   Usuario proprietario
 * @property-read \Illuminate\Support\Collection      $accountMeta            Metadados da conta
 * @property-read \Illuminate\Support\Collection      $transactions           Transacoes da conta
 * @property-read \Illuminate\Support\Collection      $piggyBanks             Cofrinhos associados
 * @property-read \Illuminate\Support\Collection      $attachments            Anexos da conta
 * @property-read \Illuminate\Support\Collection      $notes                  Notas da conta
 */
class Account extends Model
{
    use HasFactory;
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable              = ['user_id', 'user_group_id', 'account_type_id', 'name', 'active', 'virtual_balance', 'iban', 'native_virtual_balance'];

    protected $hidden                = ['encrypted'];
    private bool $joinedAccountTypes = false;

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $accountId = (int) $value;

            /** @var User $user */
            $user      = auth()->user();

            /** @var null|Account $account */
            $account   = $user->accounts()->with(['accountType'])->find($accountId);
            if (null !== $account) {
                return $account;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o relacionamento com o usuario proprietario da conta.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todos os saldos historicos da conta.
     * Os saldos sao armazenados para diferentes moedas e periodos.
     *
     * @return HasMany Colecao de AccountBalance relacionados a esta conta
     */
    public function accountBalances(): HasMany
    {
        return $this->hasMany(AccountBalance::class);
    }

    /**
     * Retorna o tipo da conta (corrente, poupanca, cartao de credito, etc.).
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo AccountType
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Retorna todos os anexos associados a esta conta.
     * Anexos podem incluir documentos, comprovantes, etc.
     *
     * @return MorphMany Colecao polimorfica de Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get the account number.
     */
    protected function accountNumber(): Attribute
    {
        return Attribute::make(get: function () {
            /** @var null|AccountMeta $metaValue */
            $metaValue = $this->accountMeta()
                ->where('name', 'account_number')
                ->first()
            ;

            return null !== $metaValue ? $metaValue->data : '';
        });
    }

    /**
     * Retorna todos os metadados associados a esta conta.
     * Metadados incluem informacoes adicionais como numero da conta, BIC, etc.
     *
     * @return HasMany Colecao de AccountMeta relacionados a esta conta
     */
    public function accountMeta(): HasMany
    {
        return $this->hasMany(AccountMeta::class);
    }

    /**
     * Retorna o nome editavel da conta.
     * Para contas do tipo CASH, retorna string vazia pois nao sao editaveis.
     *
     * @return Attribute Atributo computado para o nome editavel
     */
    protected function editName(): Attribute
    {
        return Attribute::make(get: function () {
            $name = $this->name;
            // Contas do tipo CASH nao possuem nome editavel
            if (AccountTypeEnum::CASH->value === $this->accountType->type) {
                return '';
            }

            return $name;
        });
    }

    /**
     * Retorna todas as localizacoes geograficas associadas a esta conta.
     *
     * @return MorphMany Colecao polimorfica de Location
     */
    public function locations(): MorphMany
    {
        return $this->morphMany(Location::class, 'locatable');
    }

    /**
     * Retorna todas as notas associadas a esta conta.
     * Notas podem conter informacoes adicionais ou lembretes sobre a conta.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna todos os grupos de objetos aos quais esta conta pertence.
     * Grupos de objetos permitem organizar contas em categorias personalizadas.
     *
     * @return MorphToMany Colecao polimorfica de ObjectGroup
     */
    public function objectGroups(): MorphToMany
    {
        return $this->morphToMany(ObjectGroup::class, 'object_groupable');
    }

    /**
     * Retorna todos os cofrinhos (piggy banks) associados a esta conta.
     * Cofrinhos sao metas de poupanca vinculadas a contas.
     *
     * @return BelongsToMany Colecao de PiggyBank relacionados
     */
    public function piggyBanks(): BelongsToMany
    {
        return $this->belongsToMany(PiggyBank::class);
    }

    /**
     * Escopo de consulta para filtrar contas por tipos especificos.
     * Realiza join com a tabela account_types se necessario.
     *
     * @param EloquentBuilder $query Query builder do Eloquent
     * @param array           $types Array de tipos de conta para filtrar
     *
     * @return void
     */
    #[Scope]
    protected function accountTypeIn(EloquentBuilder $query, array $types): void
    {
        // Realiza o join apenas uma vez para evitar duplicacao
        if (false === $this->joinedAccountTypes) {
            $query->leftJoin('account_types', 'account_types.id', '=', 'accounts.account_type_id');
            $this->joinedAccountTypes = true;
        }
        $query->whereIn('account_types.type', $types);
    }

    /**
     * Define o valor do saldo virtual da conta.
     * Converte valores vazios para null para consistencia no banco de dados.
     *
     * @param mixed $value Valor do saldo virtual a ser definido
     *
     * @return void
     */
    public function setVirtualBalanceAttribute(mixed $value): void
    {
        $value                               = (string) $value;
        if ('' === $value) {
            $value = null;
        }
        $this->attributes['virtual_balance'] = $value;
    }

    /**
     * Retorna todas as transacoes associadas a esta conta.
     *
     * @return HasMany Colecao de Transaction relacionadas a esta conta
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Retorna o grupo de usuarios ao qual esta conta pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo UserGroup
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Accessor para garantir que o ID da conta seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da conta
     */
    protected function accountId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do tipo de conta seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do tipo de conta
     */
    protected function accountTypeId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para formatar o IBAN removendo espacos.
     * Retorna null se o valor original for null.
     *
     * @return Attribute Atributo computado para o IBAN formatado
     */
    protected function iban(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => null === $value ? null : trim(str_replace(' ', '', (string) $value)),
        );
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
     * Accessor para garantir que o saldo virtual seja retornado como string.
     *
     * @return Attribute Atributo computado para o saldo virtual
     */
    protected function virtualBalance(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Define os casts de atributos do modelo.
     * Especifica como cada atributo deve ser convertido ao ser acessado.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'created_at'             => 'datetime',
            'updated_at'             => 'datetime',
            'user_id'                => 'integer',
            'user_group_id'          => 'integer',
            'deleted_at'             => 'datetime',
            'active'                 => 'boolean',
            'encrypted'              => 'boolean',
            'virtual_balance'        => 'string',
            'native_virtual_balance' => 'string',
        ];
    }
}
