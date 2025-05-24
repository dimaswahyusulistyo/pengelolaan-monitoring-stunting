<?php

namespace App\Imports;

use App\Models\AnakStunting;
use App\Models\Determinan;
use App\Models\Desa;
use App\Models\StatusPenanganan;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Validation\Rule;

HeadingRowFormatter::default('none');

class AnakStuntingImport implements ToCollection, WithHeadingRow, WithValidation
{
	/**
	 * Handle the collection of rows from the import.
	 *
	 * @param \Illuminate\Support\Collection $collection
	 * @return void
	 */
	public function collection(Collection $collection)
	{
		// Implement your logic here, for example:
		// foreach ($collection as $row) {
		//     AnakStunting::create([
		//         // map your columns here
		//     ]);
		// }
	}

	/**
	 * Define the validation rules for each row.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			// 'column_name' => 'required',
			// Add your validation rules here
		];
	}
}