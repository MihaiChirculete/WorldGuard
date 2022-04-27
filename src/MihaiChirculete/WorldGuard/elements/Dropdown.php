<?php
declare(strict_types=1);
namespace MihaiChirculete\WorldGuard\elements;
use pocketmine\form\FormValidationException;
class Dropdown extends Element{
	/** @var string[] */
	protected $options;
	/** @var int */
	protected $default;
	/**
	 * @param string   $text
	 * @param string[] $options
	 * @param int      $default
	 */
	public function __construct(string $text, array $options, int $default = 0){
		parent::__construct($text);
		$this->options = $options;
		$this->default = $default;
	}
	/**
	 * @return array
	 */
	public function getOptions() : array{
		return $this->options;
	}
	/**
	 * @return string
	 */
	public function getSelectedOption(): ?string
	{
		if(isset($this->options[$this->value])){
            	return $this->options[$this->value];
		}
		return null;
	}
	/**
	 * @return int
	 */
	public function getDefault() : int{
		return $this->default;
	}
	/**
	 * @return string
	 */
	public function getType() : string{
		return "dropdown";
	}
	/**
	 * @return array
	 */
	public function serializeElementData() : array{
		return [
			"options" => $this->options,
			"default" => $this->default
		];
	}
	public function validate($value): void
	{
		if (isset($this->options[$value])) {
			parent::validate($value);
		}
	}
}
