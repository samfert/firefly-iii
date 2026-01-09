<?php

declare(strict_types=1);

namespace FireflyIII\Events\Model\PiggyBank;

use FireflyIII\Events\Event;
use FireflyIII\Models\PiggyBank;
use Illuminate\Queue\SerializesModels;

/**
 * Class ChangedName
 *
 * Evento disparado quando o nome de um cofrinho e alterado.
 */
class ChangedName extends Event
{
    use SerializesModels;

    public function __construct(public PiggyBank $piggyBank, public string $oldName, public string $newName) {}
}
