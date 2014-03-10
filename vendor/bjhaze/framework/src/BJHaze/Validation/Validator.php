<?php
/**
 *
 * Validator.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Validation;
use Closure, DateTime, ArrayAccess, PDO;

class Validator
{

    /**
     * Validate that an attribute is numeric.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateNumeric ($value)
    {
        return is_numeric($value);
    }

    /**
     * Validate that an attribute is an integer.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateInteger ($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate that an attribute has a given number of digits.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateLength ($value, array $parameters)
    {
        return strlen((string) $value) == $parameters[0];
    }

    /**
     * Validate that an attribute is between a given number of digits.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateLengthBetween ($value, array $parameters)
    {
        $length = strlen((string) $value);
        
        return $length >= $parameters[0] && $length <= $parameters[1];
    }

    /**
     * Validate the file size of an attribute.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateFileSizeBetween ($value, array $parameters)
    {
        if (is_file($value)) {
            $size = filesize($value);
            return $size >= $parameters[0] * 1024 && $size <= $parameters[1] * 1024;
        }
    }

    /**
     * Validate the size of an attribute is greater than a minimum value.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateNumberBetween ($value, array $parameters)
    {
        $options = array(
                'options' => array(
                        'min_range' => $parameters[0],
                        'max_range' => $parameters[1]
                )
        );
        return false !== filter_var($value, FILTER_VALIDATE_INT, $options);
    }

    /**
     * Validate an attribute is contained within a list of values.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateIn ($value, array $parameters)
    {
        return in_array($value, $parameters);
    }

    /**
     * Validate an attribute is not contained within a list of values.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateNotIn ($value, array $parameters)
    {
        return ! in_array($value, $parameters);
    }

    /**
     * Validate the uniqueness of an attribute value on a given database table.
     *
     * If a database column is not specified, the attribute will be used.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateUnique ($value, $parameters)
    {
        $db = $parameters[0]; // pdo instance
        $table = $parameters[1]; // table name
        $column = $parameters[2]; // unique column name
        
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE $column = ?";
        $params = [
                $value
        ];
        
        $exclude = isset($parameters[3]) ? $parameters[3] : array();
        // record without check, used for some foolish update self operation.
        foreach ($exclude as $key => $value) {
            $sql .= " AND $key <> ?";
            $params[] = $value;
        }
        
        $statement = $db->prepare($sql);
        foreach ($params as $k => $value)
            $statement->bindParam($k + 1, $value, 
                    is_int($value) ? PDO::PARAM_INT : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR));
        
        $statement->execute();
        $result = $statement->fetchColumn();
        
        unset($statement);
        
        return $result == 0;
    }

    /**
     * Validate that an attribute is a valid IP.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateIp ($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateEmail ($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate that an attribute is a valid URL.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateUrl ($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate that an attribute is an active URL.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function validateActiveUrl ($value)
    {
        $url = str_replace(
                array(
                        'http://',
                        'https://',
                        'ftp://'
                ), '', strtolower($value));
        
        return checkdnsrr($url);
    }

    /**
     * Validate the MIME type of a file is an image MIME type.
     *
     * @param string $value
     * @return bool
     */
    public function validateImage ($value)
    {
        return $this->validateIn(pathinfo($value, PATHINFO_EXTENSION), 
                array(
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'bmp'
                )) && $this->validateMimes($value, 
                array(
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/bmp'
                ));
    }

    /**
     * Validate the MIME type of a file upload attribute is in a set of MIME types.
     *
     * @param string $attribute
     * @param array $value
     * @param array $parameters
     * @return bool
     */
    public function validateMimes ($value, array $parameters)
    {
        if (is_file($value))
            return in_array((new \finfo(FILEINFO_MIME_TYPE))->file($value), $parameters);
    }

    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateAlpha ($value)
    {
        return preg_match('/^\pL+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateAlphaNum ($value)
    {
        return preg_match('/^[\pL\pN]+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateAlphaDash ($value)
    {
        return preg_match('/^[\pL\pN_-]+$/u', $value);
    }

    /**
     * Validate that an attribute passes a regular expression check.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateRegex ($value, $parameters)
    {
        return preg_match($parameters[0], $value);
    }

    /**
     * Validate that an attribute is a valid date.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateDate ($value)
    {
        if ($value instanceof DateTime)
            return true;
        
        if (strtotime($value) === false)
            return false;
        
        $date = date_parse($value);
        
        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Validate that an attribute matches a date format.
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateDateFormat ($value, $parameters)
    {
        $parsed = date_parse_from_format($parameters[0], $value);
        
        return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
    }

    /**
     * Validate that an attribute matches is a float.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateFloat ($value)
    {
        return false !== filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Validate that an attribute matches base64 string.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateBase64 ($value)
    {
        return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $value);
    }

    /**
     * Validate that an attribute matches uuid string.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateUuid ($value)
    {
        $regex = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
        return (bool) preg_match($regex, $value);
    }
}
