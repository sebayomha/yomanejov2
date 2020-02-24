import { Component } from '@angular/core';
import { BreakpointObserver } from '@angular/cdk/layout';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from '../../models/response';

@Component({
  selector: 'lessons',
  templateUrl: './lessons.component.html',
  styleUrls: ['./lessons.component.scss']
})

export class  LessonsComponent {

  constructor(private breakpointObserver: BreakpointObserver, private cronogramaService: CronogramaService) { }

  displayedColumns: string[] = ['No', 'hora', 'direccion', 'alumno'];
  select_day : Date;

  autos;
  ngOnInit() {
    console.log(this.formatDate());
    this.cronogramaService.obtenerClasesPorFecha(this.formatDate()).subscribe( (response: Response) => {
      this.autos = Object.entries(response.data);
      this.obtenerClasesPorRealizarse(this.autos);
      this.obtenerClasesRealizadas(this.autos);
      console.log(this.autos);
    })
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  formatDate() {
    var d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
  }

  obtenerClasesRealizadas(autos) {
    const today = new Date();
    autos.forEach( auto => {
      auto.clasesRealizadas = auto[1].filter( clase => {
        var horaInicioDate = new Date(clase.fecha.replace('-', '/'));
        horaInicioDate.setHours(clase.horaInicio.split(':')[0],clase.horaInicio.split(':')[1],0);
        if (today >= horaInicioDate) {
          return true;
        }
        return false;
      })
    })
  }

  obtenerClasesPorRealizarse(autos) {
    const today = new Date();
    autos.forEach( auto => {
      auto.clasesPorRealizarse = auto[1].filter( clase => {
        var horaInicioDate = new Date(clase.fecha.replace('-', '/'));
        horaInicioDate.setHours(clase.horaInicio.split(':')[0],clase.horaInicio.split(':')[1],0);
        console.log(today);
        console.log(horaInicioDate);
        if (today < horaInicioDate) {
          return true;
        }
        return false;
      })
    })
  }
}