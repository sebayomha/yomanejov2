import { Pipe, PipeTransform } from '@angular/core';

@Pipe({name: 'documentEdit'})
export class DocumentoEditPipe implements PipeTransform {
  transform(value): string {
    if (value) {
      
      const valueCopy = value;
      let primerParte = valueCopy.substring(0,2);
      if (primerParte.length < 2) {
        return `${primerParte}`;
      } else {
        if (primerParte.length == 2) {
          let segundaParte = valueCopy.substring(3,6);
          if (segundaParte.length) {
            let terceraParte = valueCopy.substring(7,11);

            if (segundaParte.length == 3 && !terceraParte.length) {
              return `${primerParte}.${segundaParte}.`;
            } else {
              if (terceraParte.length) {
                return `${primerParte}.${segundaParte}.${terceraParte}`;
              } else {
                return `${primerParte}.${segundaParte}`;
              }
            }
          } else {
            return `${primerParte}.${segundaParte}`;
          }
        }
      }
    }
    return '';
  }
}