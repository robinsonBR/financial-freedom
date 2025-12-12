<?php

namespace App\Services\Loans;

use App\Models\Loan;

class UpdateLoanAccount
{
	public function __construct(
		private readonly $request,
		private readonly Loan $loan,
	) {}
+
	public function update(): void
	{
		if ($this->request->has('import_map')) {
			$this->loan->import_map = $this->request->get('import_map');
		}
+
		$this->loan->save();
	}
}
