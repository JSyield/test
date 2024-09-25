<?php

namespace Alexr\Models\Traits;

trait CsvHelpers {

	public function convertToCsv($list)
	{
		$csv = '';
		$first_row_keys = array_keys($list[0]);
		$csv .= '"' . implode('","', $first_row_keys) . '"' . PHP_EOL;

		// Every item one row
		foreach ($list as $item) {

			$item_reformatted = [];

			foreach ($item as $value) {
				// Value is an Array
				if (is_array($value)) {
					$result = [];

					foreach ($value as $sub_value) {
						if (is_string($sub_value)) {
							$result[] = $sub_value;
						} else if (is_array($sub_value)) {
							$result[] = implode(':', $sub_value);
						}
					}

					$item_reformatted[] = implode('. ', $result);
				}
				// Value is just string
				else {
					$item_reformatted[] = $value;
				}
			}
			$csv .= '"' . implode('","', $item_reformatted) . '"' . PHP_EOL;
		}

		return $csv;
	}
}
