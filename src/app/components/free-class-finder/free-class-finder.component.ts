import { Component, ViewChild, Input, Output, EventEmitter } from '@angular/core';
import { Search } from '../../models/free-class-finder.model';
import { DatesTimes } from '../../models/dates-times';
import { Option } from '../../models/option';
import { CronogramaService } from '../../services/cronograma/cronograma.service';
import { AlumnosService } from '../../services/alumnos/alumnos.service';
import { Response } from '../../models/response';
import { BreakpointObserver } from '@angular/cdk/layout';
import { Address } from '../../models/address.model';
import { Excepcion } from 'src/app/models/excepcion';
import { ExcepcionRowTIme } from 'src/app/models/excepcion-row-time';
import { DatePipe } from '@angular/common';
import { MatSnackBar } from '@angular/material/snack-bar';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { NgForm } from '@angular/forms';

@Component({
  selector: 'free-class-finder',
  templateUrl: './free-class-finder.component.html',
  styleUrls: ['./free-class-finder.component.scss', '../../global-styles.scss'],
})

export class FreeClassFinderComponent {

  @Input() edit_cronograma;
  @Output() show_edit = new EventEmitter<any>();

  @ViewChild('forma') formaSearch : NgForm;
  
  excepciones = Array<Excepcion>();
  addresses = Array<Address>();
  addresses_alt = Array<Address>();
  minDate = new Date();
  locations = ["La Plata", "Berisso", "Ensenada"];
  predefinedHours = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
  search: Search;
  control_flag_empty:boolean = false;
  control_collapse_search:boolean = false;
  schedule_send_null:boolean = true;
  available_schedules:any;
  durationInSeconds = 3;
  student_name = '';
  flag_address_alt:boolean = false;
  numberOfClasses: Number = 0;

  edit_crono_dir_ppal:boolean = false;
  edit_crono_da_ppal:boolean = false;

  flag_excep_date_error:boolean = false;


  //ALUMNOS VARIABLES
  alumnos: Array<any>;
  selectedAlumno; 
  yaEsAlumno: boolean;


  constructor(private alumnoService: AlumnosService, private cronogramaService: CronogramaService, private breakpointObserver: BreakpointObserver, private datePipe: DatePipe, private _snackBar: MatSnackBar) { }

  ngOnInit() {
    this.addresses = [];
    this.addresses_alt = [];
    let dates_times = new Array<DatesTimes>();
    for (let i = 0; i <=6; i++) {
      let option = new Option('', '', this.predefinedHours, [], null, false);
      let options = new Array(option);
      let dateTime = new DatesTimes(this.getDay(i), false, options);
      dates_times.push(dateTime);
    }

    let street = new Address('',false);
    let street_a = new Address(undefined, false, '');
    let street_b = new Address(undefined,false, undefined, '');
    let altitud = new Address(undefined,false, undefined, undefined, '');
    let city = new Address(undefined,false, undefined, undefined, undefined, '');
    let floor =  new Address(undefined,false, undefined, undefined, undefined, '', '');
    let department = new Address(undefined,false, undefined, undefined, undefined, '', '', '');
    let obser = new Address(undefined,false, undefined, undefined, undefined, '', '', '', '');
    this.addresses.push(street, street_a, street_b, altitud, city, floor, department, obser);

    let street_alt = new Address('',false);
    let street_a_alt = new Address(undefined, false, '');
    let street_b_alt = new Address(undefined,false, undefined, '');
    let altitud_alt = new Address(undefined,false, undefined, undefined, '');
    let city_alt = new Address(undefined,false, undefined, undefined, undefined, '');
    let floor_alt =  new Address(undefined,false, undefined, undefined, undefined, '', '');
    let department_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', '');
    let obser_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', '', '');
    this.addresses_alt.push(street_alt, street_a_alt, street_b_alt, altitud_alt, city_alt, floor_alt, department_alt, obser_alt);

    this.control_collapse_search = true;

    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    this.search = new Search(this.student_name, dates_times, this.addresses, this.addresses_alt, 8, tomorrow);
    this.search.address[4].city = "La Plata";
    this.search.address_alternative[4].city = "La Plata";

    this.yaEsAlumno = false;
    this.selectedAlumno = null;

    if (!this.edit_cronograma) {
      this.alumnoService.obtenerAlumnos().subscribe( (response: Response) => {
        this.alumnos = response.data;
        console.log(this.alumnos)
      })
    }
    
    //Cargo los datos del cronograma a editar para realizar la busqueda de las opciones.
    if (this.edit_cronograma) {

      console.log(this.edit_cronograma);

      this.search.student_name = this.edit_cronograma.nombreAlumno;
      this.search.student_phone = this.edit_cronograma.telefonoAlumno;

      this.edit_cronograma.clases.forEach(clase => {

        //Armo la direccion principal
        if (!this.edit_crono_dir_ppal) {

          if (clase.calle_DirPrincipal != null) {
            this.search.address[0].street = clase.calle_DirPrincipal;
            if (clase.calle_diag_DirPrincipal == 'false') {
              this.search.address[0].diag = false;
            } else {
              this.search.address[0].diag = true;
            }
          }
          if (clase.calle_a_DirPrincipal != null) {
            this.search.address[1].street_a = clase.calle_a_DirPrincipal;
            if (clase.calle_a_diag_DirPrincipal == 'false') {
              this.search.address[1].diag = false;
            } else {
              this.search.address[1].diag = true;
            }
          }
          if (clase.calle_b_DirPrincipal != null) {
            this.search.address[2].street_b = clase.calle_b_DirPrincipal;
            if (clase.calle_b_diag_DirPrincipal == 'false') {
              this.search.address[2].diag = false;
            } else {
              this.search.address[2].diag = true;
            }
          }
          if (clase.numero_DirPrincipal != null) {
            this.search.address[3].altitud = clase.numero_DirPrincipal;
          }
          if (clase.ciudad_DirPrincipal != null) {
            this.search.address[4].city = clase.ciudad_DirPrincipal;
          }
          if (clase.departamento_DirPrincipal != null) {
            this.search.address[6].department = clase.departamento_DirPrincipal;
          }
          if (clase.floor_DirPrincipal != null) {
            this.search.address[5].floor = clase.floor_DirPrincipal;
          }
          if (clase.observaciones_DirPrincipal != null) {
            this.search.address[7].observations = clase.observaciones_DirPrincipal;
          }

          this.edit_crono_dir_ppal = true;
        }

        //Armo la direccion alternativa
        if (!this.edit_crono_da_ppal) {

          if (clase.calle_DirAlternativa != null) {
            this.search.address_alternative[0].street = clase.calle_DirAlternativa;
            if (clase.calle_diag_DirAlternativa == 'false') {
              this.search.address_alternative[0].diag = false;
            } else {
              this.search.address_alternative[0].diag = true;
            }
          }
          if (clase.calle_a_DirAlternativa != null) {
            this.search.address_alternative[1].street_a = clase.calle_a_DirAlternativa;
            if (clase.calle_a_diag_DirAlternativa == 'false') {
              this.search.address_alternative[1].diag = false;
            } else {
              this.search.address_alternative[1].diag = true;
            }
          }
          if (clase.calle_b_DirAlternativa != null) {
            this.flag_address_alt = true;
            this.search.address_alternative[2].street_b = clase.calle_b_DirAlternativa;
            if (clase.calle_b_diag_DirAlternativa == 'false') {
              this.search.address_alternative[2].diag = false;
            } else {
              this.search.address_alternative[2].diag = true;
            }
          }
          if (clase.numero_DirAlternativa != null) {
            this.search.address_alternative[3].altitud = clase.numero_DirAlternativa;
          }
          if (clase.ciudad_DirAlternativa != null) {
            this.search.address_alternative[4].city = clase.ciudad_DirAlternativa;
          }
          if (clase.departamento_DirAlternativa != null) {
            this.search.address_alternative[6].department = clase.departamento_DirAlternativa;
          }
          if (clase.floor_DirAlternativa != null) {
            this.search.address_alternative[5].floor = clase.floor_DirAlternativa;
          }
          if (clase.observaciones_DirAlternativa != null) {
            this.search.address_alternative[7].observations = clase.observaciones_DirAlternativa;
          }

          this.edit_crono_da_ppal = true;
        }

        console.log(this.search);

      });

      let index = 0;
      //Recorro las disponibilidades horarias del usuario.
      Object.values(this.edit_cronograma.disponibilidades).forEach( (opc:any) => {
        if (opc.todoElDia || opc.tramosHorarios.length > 0) {
          if (opc.todoElDia) {
            this.search.dates_times[index].all_day = true;
            this.search.dates_times[index].option[0].scheduleSend = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];

            if (opc.usandoDirAlternativa) {
              this.search.dates_times[index].option[0].dir_alt = true;
            }

          } else {
            let jindex = 0;
            opc.tramosHorarios.forEach(tramo => {

              if (jindex == 0) {

                this.search.dates_times[index].option[jindex].scheduleFrom = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
                this.search.dates_times[index].option[jindex].scheduleSend = tramo.horarios;
                this.search.dates_times[index].option[jindex].hour_start = tramo.horarios[0];
                let ultimo = tramo.horarios.length;
                ultimo = ultimo - 1
                this.search.dates_times[index].option[jindex].hour_finish = tramo.horarios[ultimo].trim();

                this.search.dates_times[index].option[jindex].scheduleFrom.forEach( h => {
                  if (h > tramo.horarios[0].trim()) {
                    this.search.dates_times[index].option[jindex].scheduleTo.push(h);
                  }
                })
                this.search.dates_times[index].option[jindex].dir_alt = tramo.usandoDirAlternativa;

              } else {

                let ultimo = tramo.horarios.length;
                ultimo = ultimo - 1
                let horario_finish_anterior = jindex - 1;
                let horario_finish_actual = this.search.dates_times[index].option[horario_finish_anterior].hour_finish;
                let all_horarios = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];

                //Creo nueva opcion dento del array de opciones.
                this.search.dates_times[index].option.push({
                  hour_start: tramo.horarios[0],
                  hour_finish: tramo.horarios[ultimo].trim(),
                  scheduleFrom: [],
                  scheduleTo: [],
                  scheduleSend: tramo.horarios,
                  dir_alt: tramo.usandoDirAlternativa
                });

                all_horarios.forEach( (h:string) => {
                  if (h > horario_finish_actual.trim()) {
                    this.search.dates_times[index].option[jindex].scheduleFrom.push(h);
                  }
                })

                this.search.dates_times[index].option[jindex].scheduleFrom.forEach( (h:string) => {
                  if (h > tramo.horarios[0].trim()) {
                    this.search.dates_times[index].option[jindex].scheduleTo.push(h);
                  }
                })
                
              }

              jindex += 1;
            });
          }
        }
        index += 1;
      });

      let indexcep = 0;
      if (this.edit_cronograma.excepciones.length > 0) {
        if (this.edit_cronograma.excepciones[0].idExcepcion != null && this.edit_cronograma.excepciones[0].fecha != null ) {
          Object.values(this.edit_cronograma.excepciones).forEach( (excep:any) => {

            let date = new Date(excep.fecha.replace("-", "/"));
    
            let rowTime = new Array<ExcepcionRowTIme>({'hour_start':'', 'hour_finish':'', 'horariosDesde': this.predefinedHours, 'horariosHasta': [], 'horariosTotales': [], 'dir_alt':false});
            let newExcepcion = {'date': date, 'date_string': '', 'no_puede': excep.no_puede, 'horarios': rowTime};
            this.excepciones.push(newExcepcion);
    
            if (excep.no_puede != true) {
    
              let indexhorario = 0;
              excep.horarios.forEach( horario => {
    
                let hour_start = horario.tramoHorario[0];
                let size = horario.tramoHorario.length - 1;
                let hour_finish = horario.tramoHorario[size].trim();
    
                let array_horarios_hasta = [];
                let array_horarios_desde = [];
    
                if (indexhorario == 0) {
    
                  this.predefinedHours.forEach( h => {
                    if (h > hour_start.trim()) {
                      array_horarios_hasta.push(h);
                    }
                  })
    
                  this.excepciones[indexcep].horarios[indexhorario].hour_start = hour_start;
                  this.excepciones[indexcep].horarios[indexhorario].hour_finish = hour_finish;
                  this.excepciones[indexcep].horarios[indexhorario].horariosDesde = this.predefinedHours;
                  this.excepciones[indexcep].horarios[indexhorario].horariosHasta = array_horarios_hasta;
                  this.excepciones[indexcep].horarios[indexhorario].horariosTotales = horario.tramoHorario;
                  this.excepciones[indexcep].horarios[indexhorario].dir_alt = horario.usandoDirAlt;
    
                } else {
    
                  let horario_finish_anterior = indexhorario - 1;
                  let sizeTramo = excep.horarios[horario_finish_anterior].tramoHorario.length - 1;
                  let horario_finish_actual = excep.horarios[horario_finish_anterior].tramoHorario[sizeTramo];
    
                  this.predefinedHours.forEach( (h:string) => {
                    if (h > horario_finish_actual.trim()) {
                      array_horarios_desde.push(h);
                    }
                  })
      
                  array_horarios_desde.forEach( (h:string) => {
                    if (h > hour_start) {
                      array_horarios_hasta.push(h);
                    }
                  })
                  
                  this.excepciones[indexcep].horarios.push({
                    hour_start : hour_start,
                    hour_finish : hour_finish,
                    horariosDesde : array_horarios_desde,
                    horariosHasta : array_horarios_hasta,
                    horariosTotales : horario.tramoHorario,
                    dir_alt : horario.usandoDirAlt
                  });
    
                }
    
                indexhorario += 1;
    
              });
    
            }
    
            indexcep += 1;
    
          });
        }
      }

      this.excepciones.forEach ( (excep) => {
        let dia_actual = new Date()
        if (new Date(excep.date) < dia_actual) {
           this.flag_excep_date_error = true;
        }
      })
      

      this.schedule_send_null = false;
      this.search.lessons = this.edit_cronograma.clases.length;

      let day_actual = new Date();
      let dd = day_actual.getDate();
      let mm = day_actual.getMonth()+1;
      let yyyy = day_actual.getFullYear();
      let format_day_actual = parseInt(yyyy+''+mm+''+dd);

      let date_search = new Date(this.edit_cronograma.fechaHoraGuardado);
      let dde = date_search.getDate();
      let mme = date_search.getMonth()+1;
      let yyyye = date_search.getFullYear();
      let format_date_search = parseInt(yyyye+''+mme+''+dde);

      if (format_date_search < format_day_actual) {
        this.search.date = new Date();
      } else {
        this.search.date = new Date(this.edit_cronograma.fechaHoraGuardado);
      }
    }
  }

  checkDates() {
    if (this.edit_cronograma) {
      this.flag_excep_date_error = false;
      this.excepciones.forEach ( (excep) => {
        let day_actual = new Date();
        let dd = day_actual.getDate();
        let mm = day_actual.getMonth()+1;
        let yyyy = day_actual.getFullYear();
        let format_day_actual = parseInt(yyyy+''+mm+''+dd);

        let excep_day = new Date(excep.date);
        let dde = excep_day.getDate();
        let mme = excep_day.getMonth()+1;
        let yyyye = excep_day.getFullYear();
        let format_excep_day = parseInt(yyyye+''+mme+''+dde);

        if (format_excep_day < format_day_actual) {
           this.flag_excep_date_error = true;
        } 
      });
    }
  }

  getDay(i: number) {
    switch (i) {
      case 0:
        return 'Lunes';
      case 1:
        return 'Martes';
      case 2:
        return 'Miércoles';
      case 3:
        return 'Jueves';
      case 4:
        return 'Viernes';
      case 5:
        return 'Sábado';
      case 6:
        return 'Domingo';
    }
  }

  searchSchedules() {

    this.control_collapse_search = false;

    let object = JSON.parse(JSON.stringify(this.search));

    //Transformo los nombres de los dias en ingles
    for (let i = 0; i <=6; i++) {
      switch (i) {
        case 0:
          object.dates_times[i].name_day = 'Monday';
          break;
        case 1:
          object.dates_times[i].name_day = 'Tuesday';
          break;
        case 2:
          object.dates_times[i].name_day = 'Wednesday';
          break;
        case 3:
          object.dates_times[i].name_day = 'Thursday';
          break;
        case 4:
          object.dates_times[i].name_day = 'Friday';
          break;
        case 5:
          object.dates_times[i].name_day = 'Saturday';
          break;
        case 6:
          object.dates_times[i].name_day = 'Sunday';
          break;
      }
    }

    this.numberOfClasses = this.search.lessons;
    this.setExceptionHours();

    this.cronogramaService.getCronograma(object, this.excepciones).subscribe( (response: Response) => {
      console.log(response)
      switch (response.code) {
        case 0:
          this.available_schedules = Object.values(response.data);
          this._snackBar.dismiss();
          break;
        case 2:
        case 3:{
          this.available_schedules = null;
          this.control_collapse_search = true;
          this._snackBar.openFromComponent(SnackbarComponent, {
            duration: this.durationInSeconds * 1100,
            data: response
          });
        }
        break;
      }
    });
  }

  onAvailableSchedulesFinish($event) {
    if ($event == "GuardarCronograma") {
      this.available_schedules = null;
      this.control_collapse_search = true;
      this.excepciones = [];
      this.formaSearch.resetForm();
      this.yaEsAlumno = false;
      this.ngOnInit();
    } else {
      this.verCronosPendientes(false);
    }
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  onSwipeLeft(tabulator) {
    if(tabulator.selectedIndex < tabulator._tabs.length){
      tabulator.selectedIndex++;
    }
  }

  onSwipeRight(tabulator) {
    if(tabulator.selectedIndex > 0){
      tabulator.selectedIndex--;
    }
  }

  addException() {
    let rowTime = new Array<ExcepcionRowTIme>({'hour_start':'', 'hour_finish':'', 'horariosDesde': this.predefinedHours, 'horariosHasta': [], 'horariosTotales': [], 'dir_alt':false});
    let newExcepcion = {'date': new Date(), 'date_string': '', 'no_puede': false, 'horarios': rowTime};
    this.excepciones.push(newExcepcion);
  }

  removeExcepcion(excepcionIndex) {
    this.excepciones.splice(excepcionIndex, 1);
  }

  addNewRowTime(excepcionIndex, index){
    let hour_finish_prev = this.excepciones[excepcionIndex].horarios[index].hour_finish;  
    let new_array_options = [];

    this.predefinedHours.forEach( (h:string) => {
      if (h > hour_finish_prev.trim()) {
        new_array_options.push(h);
      }
    })

    let rowTime: ExcepcionRowTIme = {'hour_start':'', 'hour_finish':'', 'horariosDesde': new_array_options, 'horariosHasta': [], 'horariosTotales': [], 'dir_alt':false};
    this.excepciones[excepcionIndex].horarios.push(rowTime);
  }

  removeRowTime(excepcionIndex, rowRemove){
    this.excepciones[excepcionIndex].horarios.splice(rowRemove, 1);
    if (this.excepciones[excepcionIndex].horarios.length == 0) {
      this.removeExcepcion(excepcionIndex);
    }
  }

  doExcepcionHours(rowTImeIndex, excepcionIndex, rowExcepcionIndex) {
    this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosHasta = [];
    this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosDesde.forEach( (h:string) => {
      if (h > rowTImeIndex) {
        this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosHasta.push(h);
      }
    })
  }

  setExceptionHourFinish(rowTImeIndex, excepcionIndex, rowExcepcionIndex) {
    let hour_start = this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].hour_start;
    let hour_finish = this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].hour_finish;

    //Armo el array final a enviar.
    this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosDesde.forEach( (h:string) => {
      if (h >= hour_start && h <= hour_finish) {
        this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosTotales.push(h);
      }
    })
  }

  setExceptionHours() {
    //Armo el array final a enviar.
    if (this.excepciones.length) {
      this.excepciones.forEach( (excepcion: Excepcion) => {
        excepcion.date_string = this.datePipe.transform(excepcion.date, 'yyyy-MM-dd');
        if (!excepcion.no_puede) {
          excepcion.horarios.forEach( (horario: ExcepcionRowTIme) => {
            horario.horariosTotales = horario.horariosDesde.filter( (hora: String) => {
              if (hora >= horario.hour_start && hora <= horario.hour_finish) {
                return true;
              }
              return false;
            })
          })
        }
      })
    }
  }

  allDay(day) {

      let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

      if (index != -1) {
        if (this.search.dates_times[index].all_day == false ) {
          this.search.dates_times[index].all_day = true;
          this.schedule_send_null = false;

          if (this.search.dates_times[index].option.length > 1) {
            let length = this.search.dates_times[index].option.length;
            this.search.dates_times[index].option.splice( 1, length );
          }

          this.search.dates_times[index].option[0].hour_start = '';
          this.search.dates_times[index].option[0].hour_finish = '';
          this.search.dates_times[index].option[0].scheduleFrom = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
          this.search.dates_times[index].option[0].scheduleTo = [];
          this.search.dates_times[index].option[0].scheduleSend = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
          this.search.dates_times[index].option[0].dir_alt = false;

        } else {
          this.search.dates_times[index].all_day = false;
          this.search.dates_times[index].option[0].scheduleSend = null;
          this.search.dates_times[index].option[0].dir_alt = false;

          this.schedule_send_null = true;
          for (let i = 0; i <=6; i++) {
            if (this.search.dates_times[i].all_day == false) {
              if(this.search.dates_times[i].option[0].scheduleSend != null) {
                this.schedule_send_null = false;
              }
            } else {
              this.schedule_send_null = false;
            }
          }
          this.control_flag_empty = false;
        }
      }
  }

  quitDA(){
    //Esta funcion resetea todas las direcciones alternativas.
    if (!this.flag_address_alt) {
      for (let i = 0; i <=6; i++) {
        this.search.dates_times[i].option.forEach(opt => {
          if ( opt.dir_alt == true ) {
            opt.dir_alt = false;
          }
        });
      }
      this.excepciones.forEach(excep => {
        excep.horarios.forEach(horario => {
          if (horario.dir_alt) {
            horario.dir_alt = false;
          }
        });
      });
    }
  }


  addDateTime(day) {
      let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

      if (index != -1) {
        if(this.search.dates_times[index].option.length == 1 && this.search.dates_times[index].option[0].hour_start == '' && this.search.dates_times[index].option[0].hour_finish == ''){

            this.search.dates_times[index].option[0].hour_start = null;
            this.search.dates_times[index].option[0].hour_finish = null;
            this.search.dates_times[index].option[0].scheduleFrom = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
            this.search.dates_times[index].option[0].scheduleTo = [];
            this.search.dates_times[index].option[0].scheduleSend = null;

        } else {
          let option_length = this.search.dates_times[index].option.length;

          let j = option_length - 1;

          let hour_finish_selected = this.search.dates_times[index].option[j].hour_finish;

          let new_schedule_from = [];

          this.search.dates_times[index].option[j].scheduleFrom.forEach( (h:string) => {
            if (h > hour_finish_selected ) {
              new_schedule_from.push(h);
            }
          })

          this.search.dates_times[index].option.push({
            hour_start: null,
            hour_finish: null,
            scheduleFrom: new_schedule_from,
            scheduleTo: [],
            scheduleSend: null,
            dir_alt: false
          });
        }
      }
      this.control_flag_empty = true;
  }

  doScheduleTo(day,hour,index) {
    this.search.dates_times.forEach(element => {
      if (element.name_day == day) {
        element.option[index].hour_start = hour;
        element.option[index].scheduleTo = [];
        element.option[index].scheduleFrom.forEach( (h:string) => {
          if (h > hour) {
            element.option[index].scheduleTo.push(h);
          }
        })
      }
    })
  }

  setHourFinish(option,hour) {
    option.hour_finish = hour;
    this.control_flag_empty = false;
    option.scheduleSend = [];

    //Armo el array final a enviar.
    option.scheduleFrom.forEach( (h:string) => {
      if (h >= option.hour_start && h <= option.hour_finish) {
        option.scheduleSend.push(h);
      }
    })

    this.schedule_send_null = false;

  }

  selectionCity(city) {
    this.search.address[4].city = city;
  }

  selectionCityAlt(city) {
    this.search.address_alternative[4].city = city;
  }

  removeOption(index, day_options) {

    if (day_options.length > 1) {
    day_options.splice( index, 1 );
    } else {
      day_options[index].hour_start = '';
      day_options[index].hour_finish = '';
      day_options[index].scheduleTo = [];
      day_options[index].scheduleSend = null;
    }
    this.control_flag_empty = false;
    this.schedule_send_null = true;

    for (let i = 0; i <=6; i++) {
      if (this.search.dates_times[i].all_day == true) {
        return this.schedule_send_null = false;
      } else {
        if(this.search.dates_times[i].option[0].scheduleSend != null) {
          this.schedule_send_null = false;
        }
      }
    }
  }

  preventLetters($event) {
    var reg = /^[0-9]+$/i;
    if (!reg.test($event.key)) {
      $event.preventDefault();
    }
  }

  //Cierro edicion
  verCronosPendientes(flag){
    this.show_edit.emit(flag);
  }
}
