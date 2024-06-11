<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TrafficViolation;
use Illuminate\Support\Facades\Schema;

class TasFile extends Model
{
    use HasFactory;

    protected $table = 'tas_files';

    protected $fillable = [
        'case_no',
        'top',
        'driver',
        'apprehending_officer',
        'violation',
        'transaction_no',
        'date_received',
        'contact_no', 
        'plate_no',
        'remarks',
        'file_attach',
        'typeofvehicle',
        'fine_fee',
        'history',
        'status', 
     
        'symbols', 
    ];

    public function setofficerAttribute($value)
    {
        $this->attributes['apprehending_officer'] = strtoupper($value);
    }
    public function relatedOfficer()
{
    return $this->hasOne(ApprehendingOfficer::class, 'officer');
}

public function relatedViolations()
{
    // Assuming 'violation' is a JSON-encoded field in the TasFile table
    return $this->hasMany(TrafficViolation::class, 'code');
}

    public function setTopAttribute($value)
    {
        $this->attributes['top'] = strtoupper($value);
    }

    // Define mutator for 'name' field
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }
    public function setViolationAttribute($value)
    {
        if (is_array($value)) {
            // Convert the array of violations to a comma-separated string
            $this->attributes['violation'] = implode(',', $value);
        } else {
            // If it's already a string, simply assign it
            $this->attributes['violation'] = $value;
        }
    }

    public function getViolationAttribute($value)
    {
        // Check if the value is already a string, then return it
        if (is_string($value)) {
            return $value;
        }
        
        // If it's an array, convert it to a string
        return $value ? implode(',', $value) : '';
    }
    public function setTransactionNoAttribute($value)
    {
        $this->attributes['transaction_no'] = strtoupper($value);
    }

    // Define mutator for 'contact_no' field
    public function setContactNoAttribute($value)
    {
        $this->attributes['contact_no'] = strtoupper($value);
    }
    public function setdriverAttribute($value)
    {
        $this->attributes['driver'] = strtoupper($value);
    }
    
    // Define mutator for 'plate_no' field
    public function setPlateNoAttribute($value)
    {
        $this->attributes['plate_no'] = strtoupper($value);
    }
    public function getHistoryAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
 
// public function setRemarksAttribute($value)
// {
//     if (is_array($value)) {
//         // Convert the array of remarks to a comma-separated string
//         $this->attributes['remarks'] = implode(',', $value);
//     } else {
//         // If it's already a string, simply assign it
//         $this->attributes['remarks'] = $value;
//     }
// }

// // Define accessor for 'remarks' field
// public function getRemarksAttribute($value)
// {
//     // Convert the comma-separated string of remarks to an array
//     return $value ? explode(',', $value) : [];
// }
public function checkCompleteness()
{
    try {
        // List of fields to check for completeness
        $fieldsToCheck = [
            'case_no',
            'driver',
            'apprehending_officer',
            'violation',
            'date_received',
            'contact_no',
            'plate_no',
            'remarks',
            'file_attach',
            'typeofvehicle',
            'fine_fee'
        ];

        // Check if any field is empty
        $incomplete = false;
        foreach ($fieldsToCheck as $field) {
            if (empty($this->$field)) {
                $incomplete = true;
                break;
            }
        }

        // Set symbols attribute accordingly
        $this->symbols = $incomplete ? 'incomplete' : 'complete';
        
        // Save the model
        $this->save();

        \Log::info('Symbols attribute updated successfully.');
    } catch (\Exception $e) {
        \Log::error('Error updating symbols attribute: ' . $e->getMessage());
        throw new \Exception('Error updating symbols attribute: ' . $e->getMessage());
    }
}

public function handleDeletion()
{
    try {
        // List of fields to check for deletion
        $fieldsToCheck = [
            'case_no',
            'driver',
            'apprehending_officer',
            'violation',
            'date_received',
            'contact_no',
            'plate_no',
            'remarks',
            'file_attach',
            'typeofvehicle',
            'fine_fee'
        ];

        // Check if any field is empty after deletion
        $incomplete = false;
        foreach ($fieldsToCheck as $field) {
            if (!isset($this->$field) || empty($this->$field)) {
                $incomplete = true;
                break;
            }
        }

        // Set symbols attribute accordingly
        if ($incomplete) {
            $this->symbols = 'incomplete';
            $this->save();
        }

        \Log::info('Symbols attribute updated to incomplete due to data deletion.');
    } catch (\Exception $e) {
        \Log::error('Error handling deletion: ' . $e->getMessage());
        throw new \Exception('Error handling deletion: ' . $e->getMessage());
    }
}
 
      public function addViolation($newViolation)
    {
        // Retrieve existing violations
        $violations = json_decode($this->violation, true) ?? [];
        // Check if the new violation already exists
        if (!in_array($newViolation, $violations)) {
            // Add the new violation if it doesn't already exist
            $violations[] = $newViolation;
            // Update the violation attribute
            $this->violation = json_encode($violations);
            // Save the model
            $this->save();
        }
    }
   
}
