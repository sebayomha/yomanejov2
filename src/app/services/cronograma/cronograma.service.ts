import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Search } from 'src/app/models/free-class-finder.model';
import { DatePipe } from '@angular/common';
import { Excepcion } from 'src/app/models/excepcion';

@Injectable({
  providedIn: 'root'
})

export class CronogramaService {

  constructor(private http: HttpClient, private datePipe: DatePipe) { }

  getCronograma(searchParameters: Search, excepciones: Array<Excepcion>) {
    const params = new HttpParams()
    .set('cantClases', searchParameters.lessons.toString())
    .set('fechaInicio', this.datePipe.transform(searchParameters.date, 'yyyy-MM-dd'))
    .set('disponibilidad', JSON.stringify(searchParameters.dates_times))
    .set('direccion', JSON.stringify(searchParameters.address))
    .set('direccion_alt', JSON.stringify(searchParameters.address_alternative))
    .set('excepciones', JSON.stringify(excepciones))
    return this.http.get('api/calcularCronograma/', {params: params});
  }

  guardarCronograma(cronograma) {
    return this.http.post('api/calcularCronograma/guardar', JSON.stringify(cronograma));
  }

  obtenerCronogramasPendientesDeConfirmar() {
    return this.http.get('api/calcularCronograma/cronogramasPendientes');
  }

  confirmarCronogramaPendiente(idCronograma) {
    const params = new HttpParams().set('idCronograma', idCronograma);
    return this.http.get('api/calcularCronograma/confirmarCronograma', {params: params});
  }

}
