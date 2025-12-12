<?php

namespace App\Services\CashAccounts;

use App\Models\CashAccount;

class UpdateCashAccount
{
	public function __construct(
		private readonly $request,
		private readonly CashAccount $cashAccount,
	) {}
+
	public function update(): void
	{
		if ($this->request->has('import_map')) {
			$this->cashAccount->import_map = $this->request->get('import_map');
		}
+
		$this->cashAccount->save();
	}
}
