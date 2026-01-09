<?php

/**
 * Location.php
 * Copyright (c) 2020 james@firefly-iii.org
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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Location
 *
 * Representa uma localizacao geografica associada a entidades do sistema.
 * Permite armazenar coordenadas de latitude e longitude para contas,
 * transacoes e outras entidades que podem ter uma localizacao fisica.
 *
 * @property int                                 $id             Identificador unico da localizacao
 * @property int                                 $locatable_id   ID da entidade associada
 * @property string                              $locatable_type Tipo da entidade associada
 * @property float|null                          $latitude       Latitude da localizacao
 * @property float|null                          $longitude      Longitude da localizacao
 * @property int|null                            $zoom_level     Nivel de zoom do mapa
 * @property \Carbon\Carbon                      $created_at     Data de criacao
 * @property \Carbon\Carbon                      $updated_at     Data de atualizacao
 * @property-read Model                          $locatable      Entidade associada
 * @property-read \Illuminate\Support\Collection $accounts       Contas nesta localizacao
 * @property-read \Illuminate\Support\Collection $transactionJournals Transacoes nesta localizacao
 */
class Location extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['locatable_id', 'locatable_type', 'latitude', 'longitude', 'zoom_level'];

    /**
     * Adiciona regras de validacao para localizacoes.
     * Define regras para latitude, longitude e nivel de zoom.
     *
     * @param array $rules Array de regras existentes
     *
     * @return array Array de regras com as regras de localizacao adicionadas
     */
    public static function requestRules(array $rules): array
    {
        $rules['latitude']   = 'numeric|min:-90|max:90|nullable|required_with:longitude';
        $rules['longitude']  = 'numeric|min:-180|max:180|nullable|required_with:latitude';
        $rules['zoom_level'] = 'numeric|min:0|max:80|nullable|required_with:latitude';

        return $rules;
    }

    /**
     * Retorna todas as contas associadas a esta localizacao.
     *
     * @return MorphMany Colecao polimorfica de Account
     */
    public function accounts(): MorphMany
    {
        return $this->morphMany(Account::class, 'locatable');
    }

    /**
     * Retorna a entidade proprietaria desta localizacao.
     *
     * @return MorphTo Relacionamento polimorfico com a entidade proprietaria
     */
    public function locatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Retorna todos os diarios de transacao associados a esta localizacao.
     *
     * @return MorphMany Colecao polimorfica de TransactionJournal
     */
    public function transactionJournals(): MorphMany
    {
        return $this->morphMany(TransactionJournal::class, 'locatable');
    }

    /**
     * Accessor para garantir que o ID da entidade localizavel seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da entidade
     */
    protected function locatableId(): Attribute
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
            'zoomLevel'  => 'int',
            'latitude'   => 'float',
            'longitude'  => 'float',
        ];
    }
}
