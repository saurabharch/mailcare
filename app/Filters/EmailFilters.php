<?php

namespace App\Filters;

use App\Inbox;
use App\Sender;

class EmailFilters extends Filters
{
	protected $filters = ['inbox', 'search', 'unread', 'favorite'];

	protected function inbox($email)
	{
		$inbox = Inbox::where('email', $email)->firstOrFail();

		return $this->builder->where('inbox_id', $inbox->id);
	}

	protected function search($keywords)
	{
		$inboxes = Inbox::where('email', 'like', "$keywords%")->pluck('id')->all();
		$senders = Sender::where('email', 'like', "$keywords%")->pluck('id')->all();

		return $this->builder
			->whereIn('inbox_id', $inboxes)
			->orWhereIn('sender_id', $senders)
			->orWhere('subject', 'like', "$keywords%");
	}

	protected function unread()
	{
		return $this->builder->whereNull('read');
	}

	protected function favorite()
	{
		return $this->builder->where('favorite', true);
	}
}