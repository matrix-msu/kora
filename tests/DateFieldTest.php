<?php

use App\DateField as DateField;

/**
 * Class DateFieldTest
 * @group field
 */
class DateFieldTest extends TestCase
{
    /**
     * The options for the different cases of a possible date field's circa/era options.
     * @type array
     */
    const DATE = <<<TEXT
[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]
TEXT;
    const DATE_ERA = <<<TEXT
[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]Yes[!Era!]
TEXT;
    const DATE_CIRCA = <<<TEXT
[!Circa!]Yes[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]
TEXT;
    const DATE_ERA_CIRCA = <<<TEXT
[!Circa!]Yes[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]Yes[!Era!]
TEXT;


    /**
     * Test keyword search for a date field.
     * @group search
     */
    public function test_keywordSearch() {
        $project = self::dummyProject();
        $this->assertInstanceOf('App\Project', $project);

        $form = self::dummyForm($project->pid);
        $this->assertInstanceOf('App\Form', $form);

        $field = self::dummyField("Date", $project->pid, $form->fid);
        $this->assertInstanceOf('App\Field', $field);

        $record = self::dummyRecord($project->pid, $form->fid);
        $this->assertInstanceOf('App\Record', $record);

        //
        // Test with circa and era options off at the field level.
        //
        $field->options = self::DATE;
        $field->save();

        $date_field = new DateField();
        $date_field->rid = $record->rid;
        $date_field->flid = $field->flid;
        $date_field->circa = 1; // Try with circa on
        $date_field->month = 10;
        $date_field->day = 9;
        $date_field->year = 1994;
        $date_field->era = "CE";
        $date_field->save();

        $args = ['CE', 'cE', 'Ce', 'ce']; // Era is off, so this should not work.
        $this->assertFalse($date_field->keywordSearch($args, false));

        $args = ['Circa', 'circa', 'cIrCa']; // Circa is off, so this should not work.
        $this->assertFalse($date_field->keywordSearch($args, false));

        $date_field->circa = 0; // Try with circa off.
        $date_field->save();

        $args = ['CE', 'cE', 'Ce', 'ce'];
        $this->assertFalse($date_field->keywordSearch($args, false));

        $args = ['Circa', 'circa', 'cIrCa'];
        $this->assertFalse($date_field->keywordSearch($args, false));

        $args = ['oCtObEr'];
        $this->assertTrue($date_field->keywordSearch($args, false));

        $args = ['ocTuBrE']; // October in Spanish.
        $this->assertTrue($date_field->keywordSearch($args, false));

        $args = ['oCtOBRE']; // October in French.
        $this->assertTrue($date_field->keywordSearch($args, false));

        $args = [9];
        $this->assertTrue($date_field->keywordSearch($args, false));

        $args = ['9'];
        $this->assertTrue($date_field->keywordSearch($args, false));

        $args = [94]; // Make sure the year isn't matched by subsets of the year.
        $this->assertFalse($date_field->keywordSearch($args, false));

        $args = ['94', '19', 19];
        $this->assertFalse($date_field->keywordSearch($args, false));

        //
        // Test with circa on at the field level.
        //
        $field->options = self::DATE_CIRCA;
        $field->save();



        //
        // Test with era options on at the field level.
        //



        //
        // Test with era and circa option turned on.
        //
    }

    /**
     * Test the month to number function in the date field class.
     */
    public function test_monthToNumber() {
        $englishMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

        for ($i = 0; $i < count($englishMonths); $i++) {
            $monthNumber = DateField::monthToNumber($englishMonths[$i]);
            $this->assertEquals($monthNumber, $i + 1);
        }

        $spanishMonths = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        for ($i = 0; $i < count($spanishMonths); $i++) {
            $monthNumber = DateField::monthToNumber($spanishMonths[$i]);
            $this->assertEquals($monthNumber, $i + 1);
        }

        $frenchMonths = ['janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre'];

        for ($i = 0; $i < count($frenchMonths); $i++) {
            $monthNumber = DateField::monthToNumber($frenchMonths[$i]);
            $this->assertEquals($monthNumber, $i + 1);
        }
    }
}