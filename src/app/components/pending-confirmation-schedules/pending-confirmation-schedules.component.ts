import { Component, OnInit } from '@angular/core';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from '../../models/response';
import { BreakpointObserver } from '@angular/cdk/layout';

@Component({
  selector: 'app-pending-confirmation-schedules',
  templateUrl: './pending-confirmation-schedules.component.html',
  styleUrls: ['./pending-confirmation-schedules.component.css']
})
export class PendingConfirmationSchedulesComponent implements OnInit {

  constructor(private cronogramaService: CronogramaService, private breakpointObserver: BreakpointObserver) { }

  cronogramas: Array<any>;
  displayedColumns: string[] = ['noClase', 'fecha', 'hora', 'direccion', 'auto'];

  ngOnInit() {
    this.cronogramaService.obtenerCronogramasPendientesDeConfirmar().subscribe( (response: Response) => {
      this.cronogramas = response.data;
      console.log(response);
    })
  }

  inicialesNombreAlumno(nombreAlumno: string) {
    let nombreAlumnoArr = nombreAlumno.split(' ');
    let iniciales: string = '';
    nombreAlumnoArr.forEach(element => {
      iniciales += element[0];
    });

    return iniciales;
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  sendWsp(numeroTelefono: string) {
    numeroTelefono = numeroTelefono.replace(/\s/g, "").replace('-', "");
    if (this.isMobile()) {
      window.open("https://wa.me/54"+numeroTelefono, "_blank");
    }
    else {
      window.open("https://web.whatsapp.com/send?phone=+54"+numeroTelefono, "_blank");
    } 
    console.log("SENDWSP")
  }

}
