<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG Node Transformable - add transformation (translate/rotate/scale) to node
//	should be used with SVGNode class
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');
require_once(realpath(__DIR__) . DIRECTORY_SEPARATOR . 'php_matrices/.php_matrices_autoload');

use php_matrices\Matrices3x3;
//------------------------------------------------------------------------------------------------------------------------
trait SVGNodeTransformable {

	public function scale(float $x = 1.0, float $y = 1.0): void {
		// scale node
		$matrix = Matrices3x3::scale($x, $y);
		$this->add_transform($matrix);
	}

	public function rotate(float $a_deg, float $x = 0.0, float $y = 0.0): void {
		// rotate node
		$matrix = Matrices3x3::rotate($a_deg, $x, $y);
		$this->add_transform($matrix);
	}

	public function translate(float $x = 0.0, float $y = 0.0): void {
		// translate node to ($x, $y)
		$matrix = Matrices3x3::translate($x, $y);
		$this->add_transform($matrix);
	}

	public function set_transform($transform): void {
		// set transform to node
		if(is_array($transform)) {
			// $transform is the transform matrix (Ex: [[0,0,0], ...])
			$this->set_attribute('transform', $this->to_svg_matrix_str($transform));
		} else if(is_string($transform)) {
			// $transform is the transform string (Ex: "rotate(90)")
			$this->set_attribute('transform', $transform);
		}
	}

	public function add_transform(array $matrix): void {
		// append transform to node from matrix3x3 if it already has transformatoins
		if($matrix) {
			if($this->attribute('transform')) {
				// node already has transformations
				$transform_matrix = $this->get_transform();
				$joined_matrix = Matrices3x3::multiply($transform_matrix, $matrix);
				$this->set_transform($joined_matrix);
			} else {
				// no existed transformations - just set new
				$this->set_transform($matrix);
			}
		}
	}

	public function transform_from($node): void {
		// set transform from node $node
		if($transform = $node->attribute('transform')) {
			$this->set_attribute('transform', $transform);
		}
	}

	public function get_transform() {
		// parse svg transform attribute to matrix 3x3
		//	supports
		//		transform="matrix(0.0,0.0,0.0,0.0,0.0,0.0)"
		//		transform="rotate(90)"
		//		transform="scale(-1)"
		//		transform="translate(10, 20)"
		//	ToDo:
		//		combinations, ex: transform="rotate(90) scale(-1) translate(10)"
		if($transform_attribute = $this->attribute('transform')){
			if(mb_substr($transform_attribute, 0, strlen('matrix')) === 'matrix') {
				return static::from_svg_matrix_str($transform_attribute);
			} elseif (mb_substr($transform_attribute, 0, strlen('rotate')) === 'rotate') {
				return static::from_svg_rotate_str($transform_attribute);
			} elseif (mb_substr($transform_attribute, 0, strlen('scale')) === 'scale') {
				return static::from_svg_scale_str($transform_attribute);
			} elseif (mb_substr($transform_attribute, 0, strlen('translate')) === 'translate') {
				return static::from_svg_translate_str($transform_attribute);
			}
		}
		return NULL;
	}

	public static function to_svg_matrix_str(array $matrix): string {
		// return matrix as transformation .svg matrix string
		$matrix_str = $matrix[0][0] . ',';
		$matrix_str .= $matrix[1][0] . ',';
		$matrix_str .= $matrix[0][1] . ',';
		$matrix_str .= $matrix[1][1] . ',';
		$matrix_str .= $matrix[0][2] . ',';
		$matrix_str .= $matrix[1][2];
		return 'matrix(' . $matrix_str . ')';
	}

	private static function from_svg_matrix_str(string $matrix_str): array {
		// get matrix from transformation .svg matrix string, ex: "matrix(0.0,0.0,0.0,0.0,0.0,0.0)"
		if(mb_substr($matrix_str, 0, strlen('matrix')) === 'matrix') {
			$data = mb_substr($matrix_str, strlen('matrix') + 1, -1);	// remove "matrix(" and ")"
			$data_array = explode(',', $data);
			$data_array = array_map('floatval', $data_array);
			return [
				[$data_array[0], $data_array[2], $data_array[4]],
				[$data_array[1], $data_array[3], $data_array[5]],
				[0.0, 0.0, 1.0]
			];
		} else return [];
	}

	private static function from_svg_rotate_str(string $rotate_str): array {
		// get matrix from transformation .svg rotate string, ex: "rotate(90, 1.0, 1.0)"
		if(mb_substr($rotate_str, 0, strlen('rotate')) === 'rotate') {
			$data = mb_substr($rotate_str, strlen('rotate') + 1, -1);	// remove "rotate(" and ")"
			$data_array = explode(',', $data);
			$data_array = array_map('floatval', $data_array);	// [90.0, x, y]
			$a = $data_array[0];
			$x = count($data_array) > 1 ? $data_array[1] : 0.0;
			$y = count($data_array) > 2 ? $data_array[2] : 0.0;
			return Matrices3x3::rotate($a, $x, $y);
		} else return [];
	}

	private static function from_svg_scale_str(string $scale_str): array {
		// get matrix from transformation .svg scale string, ex: "scale(-1)"
		if(mb_substr($scale_str, 0, strlen('scale')) === 'scale') {
			$data = mb_substr($scale_str, strlen('scale') + 1, -1);	// remove "scale(" and ")"
			$data_array = explode(',', $data);
			$data_array = array_map('floatval', $data_array);	// [1.0, 1.0]
			$x = $data_array[0];
			$y = count($data_array) == 1 ? $x : 0.0;
			return Matrices3x3::scale($x, $y);
		} else return [];
	}

	private static function from_svg_translate_str(string $translate_str): array {
		// get matrix from transformation .svg translate string, ex: "translate(10, 10)"
		if(mb_substr($translate_str, 0, strlen('translate')) === 'translate') {
			$data = mb_substr($translate_str, strlen('translate') + 1, -1);	// remove "translate(" and ")"
			$data_array = explode(',', $data);
			$data_array = array_map('floatval', $data_array);	// [1.0, 1.0]
			$x = $data_array[0];
			$y = count($data_array) > 1 ? $data_array[1] : 0.0;
			return Matrices3x3::translate($x, $y);
		} else return [];
	}
}
//------------------------------------------------------------------------------------------------------------------------
?>
