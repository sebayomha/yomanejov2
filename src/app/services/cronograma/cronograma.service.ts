import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Search } from 'src/app/models/free-class-finder.model';
import { DatePipe } from '@angular/common';
import { Excepcion } from 'src/app/models/excepcion';
import { BASE_URL } from '../../configVariables';

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
    }
    return this.http.post(`${BASE_URL}/api/calcularCronograma/`, {params: data});
  }

  obtenerClasesActivasCronograma(idCronograma, searchParameters: Search, excepciones?: Array<Excepcion>) {
    const data = {
      idCronograma: JSON.stringify(idCronograma),
      fechaInicio: this.datePipe.transform(searchParameters.date, 'yyyy-MM-dd'),
      disponibilidad: JSON.stringify(searchParameters.dates_times),
      excepciones: JSON.stringify(excepciones)
    }
    return this.http.post(`${BASE_URL}/api/calcularCronograma/obtenerClasesActivasCronograma`, {params: data});
  }

  guardarCronograma(cronograma) {
    return this.http.post(`${BASE_URL}/api/calcularCronograma/guardar`, JSON.stringify(cronograma));
  }

  actualizarCronogramaPendiente(cronograma) {
    return this.http.post(`${BASE_URL}/api/calcularCronograma/actualizarCronogramaPendiente`, JSON.stringify(cronograma));
  }

  actualizarCronogramaActivo(cronograma) {
    return this.http.post(`${BASE_URL}/api/calcularCronograma/actualizarCronogramaActivo`, JSON.stringify(cronograma));
  }

  obtenerCronogramasPendientesDeConfirmar() {
    return this.http.get(`${BASE_URL}/api/calcularCronograma/cronogramasPendientes`);
  }

  confirmarCronogramaPendiente(idCronograma, idAlumno, direccionFisicaInformation, documento, clases) {
    let data = {
      idCronograma: idCronograma,
      idAlumno: idAlumno,
      direccionFisicaInformation: direccionFisicaInformation,
      documento: documento,
      clases: clases
    }
    return this.http.post(`${BASE_URL}/api/calcularCronograma/confirmarCronograma`, {params: data});
  }

  cancelarCronogramaPendiente(idCronograma, idAlumno) {
    let data = {
      idCronograma: idCronograma,
      idAlumno: idAlumno
    }
    return this.http.post(`${BASE_URL}/api/calcularCronograma/cancelarCronograma`, {params: data});
  }

  cancelarCronogramaActivo(idCronograma, idAlumno, motivoBaja) {
    let data = {
      idCronograma: idCronograma,
      idAlumno: idAlumno,
      motivoBaja: motivoBaja
    }
    return this.http.post(`${BASE_URL}/api/calcularCronograma/cancelarCronogramaActivo`, {params: data});
  }

  obtenerClasesPorFecha(fecha: string) {
    const params = new HttpParams().set('fecha', fecha);
    return this.http.get(`${BASE_URL}/api/calcularCronograma/obtenerClasesPorFecha`, {params: params});
  }

  obtenerClasesDisponiblesParaAlumno(idAlumno) {
    return this.http.post(`${BASE_URL}/api/calcularCronograma/obtenerClasesDisponiblesParaAlumno`, {params: idAlumno});
  }

  agregarClaseACronograma(data) {
    return this.http.post(`${BASE_URL}/api/calcularCronograma/agregarClaseACronograma`, JSON.stringify(data));
  }

  cancelarClase(idClase, motivoCancelacion) {
    let data = {
      idClase: idClase,
      motivoCancelacion: motivoCancelacion
    }
    return this.http.post(`${BASE_URL}/api/calcularCronograma/cancelarClase`, {params: data});
  }

}
