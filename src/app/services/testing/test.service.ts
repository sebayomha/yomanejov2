import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class TestService {

  constructor(private http: HttpClient) { }

  getCronograma(nomdia, fecha) {
    const params = new HttpParams()
    .set('nomdia', nomdia)
    .set('fecha', fecha);
    return this.http.get('api/calcularCronograma/', {params: params});
  }
}
