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

  getCronograma(searchParameters: Search, excepciones?: Array<Excepcion>) {
    const data = {
      cantClases: searchParameters.lessons.toString(),
      fechaInicio: this.datePipe.transform(searchParameters.date, 'yyyy-MM-dd'),
      disponibilidad: JSON.stringify(searchParameters.dates_times),
      direccion: JSON.stringify(searchParameters.address),
      direccion_alt: JSON.stringify(searchParameters.address_alternative),
      excepciones: JSON.stringify(excepciones)
    }/* 
    const params = new HttpParams()
    .set('cantClases', searchParameters.lessons.toString())
    .set('fechaInicio', this.datePipe.transform(searchParameters.date, 'yyyy-MM-dd'))
    .set('disponibilidad', JSON.stringify(searchParameters.dates_times))
    .set('direccion', JSON.stringify(searchParameters.address))
    .set('direccion_alt', JSON.stringify(searchParameters.address_alternative))
    .set('excepciones', JSON.stringify(excepciones)) */
    return this.http.post('api/calcularCronograma/', {params: data});
  }

  guardarCronograma(cronograma) {
    return this.http.post('api/calcularCronograma/guardar', JSON.stringify(cronograma));
  }

  actualizarCronogramaPendiente(cronograma) {
    return this.http.post('api/calcularCronograma/actualizarCronogramaPendiente', JSON.stringify(cronograma));
  }

  obtenerCronogramasPendientesDeConfirmar() {
    return this.http.get('api/calcularCronograma/cronogramasPendientes');
  }

  confirmarCronogramaPendiente(idCronograma, idAlumno, direccionFisicaInformation, documento, clases) {
    let data = {
      idCronograma: idCronograma,
      idAlumno: idAlumno,
      direccionFisicaInformation: direccionFisicaInformation,
      documento: documento,
      clases: clases
    }
    return this.http.post('api/calcularCronograma/confirmarCronograma', {params: data});
  }

  cancelarCronogramaPendiente(idCronograma, idAlumno) {
    let data = {
      idCronograma: idCronograma,
      idAlumno: idAlumno
    }
    return this.http.post('api/calcularCronograma/cancelarCronograma', {params: data});
  }

  obtenerClasesPorFecha(fecha: string) {
    const params = new HttpParams().set('fecha', fecha);
    return this.http.get('api/calcularCronograma/obtenerClasesPorFecha', {params: params});
  }

}
