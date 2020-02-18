import { Pipe, PipeTransform } from '@angular/core';
/*
 * Raise the value exponentially
 * Takes an exponent argument that defaults to 1.
 * Usage:
 *   value | exponentialStrength:exponent
 * Example:
 *   {{ 2 | exponentialStrength:10 }}
 *   formats to: 1024
*/
@Pipe({name: 'phone'})
export class PhonePipe implements PipeTransform {
  transform(value): string {
    let value2;
    if (value) {
        const input = value.replace(/\D/g,'').substring(0,10); // First ten digits of input only
        let zip;
        let middle;
        let last;
        if(value.substring(0,2) == '11') {
            zip = input.substring(0,2);
            middle = input.substring(2,6);
            last = input.substring(6,11);
        } else {
            zip = input.substring(0,3);
            middle = input.substring(3,6);
            last = input.substring(6,10);
        }      
    
        if(input.length > 6){value2 = `${zip} ${middle} - ${last}`;}
        else if(input.length > 3){value2 = `${zip} ${middle}`;}
        else if(input.length > 0){value2 = `${zip}`;}
        return value2;
    }
    return ''
  }
}