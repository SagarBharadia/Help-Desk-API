<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
  private $errors = [];

  /**
   * Determine if the validation rule passes.
   *
   * @param string $attribute
   * @param mixed $value
   * @return bool
   */
  public function passes($attribute, $value)
  {
    $oneUppercaseRegex = '/[A-Z]{1,}/';
    $oneLowercaseRegex = '/[a-z]{1,}/';
    $threeDigitsRegex = '/[0-9]{3,}/';
    $twoSpecialCharsRegex = '/[\'^£$%&*()}{@#~?><>,|=_+¬\-\[\]!]{2,}/';

    $matchesOneUppercase = preg_match($oneUppercaseRegex, $value);
    $matchesOneLowercase = preg_match($oneLowercaseRegex, $value);
    $matchesThreeDigits = preg_match($threeDigitsRegex, $value);
    $matchesTwoSpecialChars = preg_match($twoSpecialCharsRegex, $value);


    if(!$matchesOneUppercase) array_push($this->errors, "Password must contain at least one uppercase character.");
    if(!$matchesOneLowercase) array_push($this->errors, "Password must contain at least one lowercase character.");
    if(!$matchesThreeDigits) array_push($this->errors, "Password must contain at least three digits.");
    if(!$matchesTwoSpecialChars) array_push($this->errors, "Password must contain at least two special characters. Special characters: '^£$%&*()}{@#~?><>,|=_+¬-[]!");
    if(strlen($value) < 8) array_push($this->errors, "Password must be at least 8 characters long.");

    return empty($this->errors);
  }

  /**
   * Get the validation error message.
   *
   * @return string
   */
  public function message()
  {
    return implode(" ", $this->errors);
  }
}