<?php
declare(strict_types=1);
namespace MihaiChirculete\WorldGuard\forms;
use Closure;
use MihaiChirculete\WorldGuard\elements\Element;
use pocketmine\{form\FormValidationException, Player, utils\Utils};
use function array_merge;
use function gettype;
use function is_array;

class CustomForm extends Form{
	/** @var Element[] */
	protected $elements;
	/** @var Closure */
	private $onSubmit;
	/** @var Closure|null */
	private $onClose;
	/**
	 * @param string       $title
	 * @param Element[]    $elements
	 * @param Closure      $onSubmit
	 * @param Closure|null $onClose
	 */
	public function __construct(string $title, array $elements, Closure $onSubmit, ?Closure $onClose = null){
		parent::__construct($title);
		$this->elements = $elements;
		$this->onSubmit = $onSubmit;
		$this->onClose = $onClose;
		Utils::validateCallableSignature(function(\WGPlayerClass $player, CustomFormResponse $response) : void{}, $onSubmit);
		$this->onSubmit = $onSubmit;
		if($onClose !== null){
		    Utils::validateCallableSignature(function(\WGPlayerClass $player) : void{}, $onClose);
			$this->onClose = $onClose;
		}
	}
	/**
	 * @param Element ...$elements
	 *
	 * @return $this
	 */
	public function append(Element ...$elements) : self{
		$this->elements = array_merge($this->elements, $elements);
		return $this;
	}
	/**
	 * @return string
	 */
	final public function getType() : string{
		return self::TYPE_CUSTOM_FORM;
	}
	/**
	 * @return array
	 */
	protected function serializeFormData() : array{
		return ["content" => $this->elements];
	}

	final public function handleResponse(\WGPlayerClass $player, $data) : void{
		if($data === null){
			if($this->onClose !== null){
				($this->onClose)($player);
			}
		}elseif(is_array($data)){
			foreach($data as $index => $value){
				if(!isset($this->elements[$index])){
					throw new FormValidationException("Element at index $index does not exist");
				}
				$element = $this->elements[$index];
				$element->validate($value);
				$element->setValue($value);
			}
			($this->onSubmit)($player, new CustomFormResponse($this->elements));
		}else{
			throw new FormValidationException("Expected array or null, got " . gettype($data));
		}
	}
}
