<?php

/**
 * SetNotes.php
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

namespace FireflyIII\TransactionRules\Actions;

use FireflyIII\Events\TriggeredAuditLog;
use FireflyIII\Models\Note;
use FireflyIII\Models\RuleAction;
use FireflyIII\Models\TransactionJournal;

/**
 * Class SetNotes.
 *
 * Acao para definir as notas de uma transacao.
 */
class SetNotes implements ActionInterface
{
    /**
     * Construtor da acao.
     *
     * @param RuleAction $action Acao da regra
     */
    public function __construct(private readonly RuleAction $action) {}

    /**
     * Executa a acao no diario de transacao.
     *
     * @param array $journal Dados do diario
     *
     * @return bool True se executado com sucesso
     */
    public function actOnArray(array $journal): bool
    {
        $dbNote       = Note::where('noteable_id', $journal['transaction_journal_id'])
            ->where('noteable_type', TransactionJournal::class)->first()
        ;
        if (null === $dbNote) {
            $dbNote                = new Note();
            $dbNote->noteable_id   = $journal['transaction_journal_id'];
            $dbNote->noteable_type = TransactionJournal::class;
            $dbNote->text          = '';
        }
        $oldNotes     = $dbNote->text;
        $newNotes     = $this->action->getValue($journal);
        $dbNote->text = $newNotes;
        $dbNote->save();

        app('log')->debug(
            sprintf(
                'RuleAction SetNotes changed the notes of journal #%d from "%s" to "%s".',
                $journal['transaction_journal_id'],
                $oldNotes,
                $newNotes
            )
        );

        /** @var TransactionJournal $object */
        $object       = TransactionJournal::where('user_id', $journal['user_id'])->find($journal['transaction_journal_id']);

        event(new TriggeredAuditLog($this->action->rule, $object, 'update_notes', $oldNotes, $newNotes));

        return true;
    }
}
