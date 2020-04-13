import { Component, OnInit, Output, EventEmitter, ViewChild } from '@angular/core';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from '../../models/response';
import { BreakpointObserver } from '@angular/cdk/layout';
import { MatSnackBar } from '@angular/material/snack-bar';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { ActivatedRoute,Router } from '@angular/router';
import { AlumnosService } from 'src/app/services/alumnos/alumnos.service';
import { trigger, transition, animate, style } from '@angular/animations';
import { SharedService } from 'src/app/services/sharedService/shared-service';
import { MatDialog} from '@angular/material';
import { AppSettings } from '../../appConstants';

@Component({
  selector: 'app-pending-confirmation-schedules',
  templateUrl: './pending-confirmation-schedules.component.html',
  styleUrls: ['./pending-confirmation-schedules.component.css'],
  animations: [
    trigger('slideInOut', [
      transition(':enter', [style({ transform: 'translateX(-100%)' }), animate('.3s ease-out', style({ transform: 'translateX(0%)' }))]),
      transition(':leave',[style({ transform: 'translateX(0%)' }), animate('.3s ease-out', style({ transform: 'translateX(100%)' }))])
    ]),
    trigger('slideIn', [
      transition(':enter', [style({ transform: 'translateX(-100%)' }), animate('.3s ease-out', style({ transform: 'translateX(0%)' }))]),
    ]),
  ]
})
export class PendingConfirmationSchedulesComponent implements OnInit {

  constructor(private dialog: MatDialog, private sharedService: SharedService ,private router: Router, private alumnoService: AlumnosService, private route: ActivatedRoute, private cronogramaService: CronogramaService, private breakpointObserver: BreakpointObserver, private _snackBar: MatSnackBar) { }

  USER_ROLE = AppSettings.USER_ROLE;

  cronogramas: Array<any> = [];
  cronogramasConfirmados: Array<any> = [];
  cronogramasFinalizados: Array<any> = [];
  cronogramasCancelados: Array<any> = [];

  cronograma_edit = [];
  displayedColumns: string[] = ['noClase', 'fecha', 'hora', 'direccion', 'auto'];
  displayedColumnsStatus: string[] = ['Realizada', 'noClase', 'fecha', 'hora', 'direccion', 'auto'];
  showSuccessBanner: boolean = false;
  dataToConfirm: any;
  durationInSeconds: number = 3;
  operation: string;
  show_edit:boolean = false;
  direccionDocumento: any = {
    direccion: '',
    documento: ''
  };

  @ViewChild('nameInput') nameInput;
  @ViewChild('nameForm') nameForm;
  @ViewChild('idForm') idForm;
  @ViewChild('idInput') idInput;
  @ViewChild('filterDialog') filterDialog;
  @Output() finish = new EventEmitter<any>();
  @ViewChild('customModal') customModal;

  isLoaded = false;
  idCronograma;
  detailedCronograma;
  sub;
  allCronogramas;
  currentTabIndex = 0;

  filterType: Array<string> = [];
  filterShowType: string = null;
  currentArrayToFilter = null;
  nombres;
  idsCronogramas;
  selectedNombreFilter: Array<any> = [];
  selectedIdCronogramaFilter: Array<any> = [];
  selectedNombresChips = new Map([ 
    [0, []], 
    [1, []], 
    [2, []],
    [3, []] 
  ]);
  selectedIdsCronogramasChips = new Map([ 
    [0, []], 
    [1, []], 
    [2, []],
    [3, []] 
  ]);

  filteredArrayCronogramas: Array<any> = [];
  filteredArrayFinalizadosCronogramas: Array<any> = [];
  filteredArrayCanceladosCronogramas: Array<any> = [];

  filteredArrayByNombre: Array<any> = [];
  filteredArrayByIdCronograma: Array<any> = [];
  sinRepetidosCronogramas: Array<any> = [];
  sinRepetidosFinalizadosCronogramas: Array<any> = [];
  sinRepetidosCanceladosCronogramas: Array<any> = [];
  nombresFiltered: Array<any> = [];
  idsCronogramasFiltered: Array<any> = [];

  ngOnInit() {
    this.cronogramaService.obtenerCronogramasPendientesDeConfirmar().subscribe( (response: Response) => {
      this.allCronogramas = response.data;
      this.cronogramas = response.data.cronogramasPendientes;
      this.cronogramasConfirmados = response.data.cronogramasConfirmados;
      this.cronogramasFinalizados = response.data.cronogramasFinalizados;
      this.cronogramasCancelados = response.data.cronogramasCancelados;
      console.log("cronogramasConfirmados", this.cronogramasConfirmados);
      this.isLoaded = true;

      
      this.sub = this.route.params.subscribe(params => {
        this.idCronograma = +params['idCronograma'];
        this.detailedCronograma = this.getDetailedCronograma(this.idCronograma);
        console.log("idCronograma::", this.idCronograma)
        console.log("detailedCronograma:: ", this.detailedCronograma);
        //ESTO VA A SER USADO PARA CUANDO SE PUEDAN FILTRAR LOS CRONOGRAMAS CON UN BUSCADOR ARRIBA
    });
    })
  }

  getDetailedCronograma(idCronograma) {
    let cronogramaPendiente = this.cronogramas.find( (cronograma) => {
      if (cronograma.idCronograma == idCronograma) {
        return true;
      }
    })
    
    let cronogramaConfirmado = this.cronogramasConfirmados.find( (cronograma) => {
      if (cronograma.idCronograma == idCronograma) {
        return true;
      }
    })

    let cronogramaFinalizado = this.cronogramasFinalizados.find( (cronograma) => {
      if (cronograma.idCronograma == idCronograma) {
        return true;
      }
    })

    let cronogramaCancelado = this.cronogramasCancelados.find( (cronograma) => {
      if (cronograma.idCronograma == idCronograma) {
        return true;
      }
    })

    return cronogramaPendiente || cronogramaConfirmado || cronogramaFinalizado || cronogramaCancelado;
  }

  ngOnDestroy() {
    this.sub.unsubscribe();
  }

  verTodosCronogramas() {
    this.detailedCronograma = null;
    this.idCronograma = null;
    this.router.navigate(['pendientes']);
  }

  inicialesNombreAlumno(nombreAlumno: string) {
    let nombreAlumnoArr = nombreAlumno.split(' ');
    let iniciales: string = '';
    nombreAlumnoArr.forEach(element => {
      iniciales += element[0];
    });

    return iniciales;
  }

  getHourAndMinutes(cronograma) {
    let endDate = new Date(cronograma.fechaHoraGuardado);
    let purchaseDate = new Date();
    let diffMs = Math.abs((purchaseDate.getTime() - endDate.getTime())); // milliseconds
    let diffDays = Math.floor(diffMs / 86400000); // days
    let diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
    let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
    return diffHrs + " horas " + diffMins + " minutos";
  }

  getHourDayAndMinutesActive(cronograma) {
    let endDate = new Date(cronograma.timestampActivo);
    let purchaseDate = new Date();
    let diffMs = Math.abs((purchaseDate.getTime() - endDate.getTime())); // milliseconds
    let diffDays = Math.floor(diffMs / 86400000); // days
    let diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
    let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
    return diffDays + " días " + diffHrs + " horas ";
  }

  getHourDayAndMinutes(cronograma) {
    let endDate = new Date(cronograma.timestampFinalizado);
    let purchaseDate = new Date();
    let diffMs = Math.abs((purchaseDate.getTime() - endDate.getTime())); // milliseconds
    let diffDays = Math.floor(diffMs / 86400000); // days
    let diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
    let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
    return diffDays + " días " + diffHrs + " horas ";
  }

  openDialog(filterType: string, arrayToFilter: Array<any>): void {
    this.dialog.open(this.filterDialog, {
      backdropClass: 'backdropBackground',
      position: { top: '210px', left: '200px' },
      width: '550px'
    });

    this.filterShowType = filterType;
    this.currentArrayToFilter = arrayToFilter;
    this.filterType.push(filterType);

    if(this.filterShowType == 'nombre') {
      this.nombres = arrayToFilter.map( (cronograma) => {
        return cronograma.nombreAlumno;
      });

      this.nombresFiltered = this.nombres.slice();
    }

    if(this.filterShowType == 'ID') {
      this.idsCronogramas = arrayToFilter.map( (cronograma) => {
        return cronograma.idCronograma;
      });

      this.idsCronogramasFiltered = this.idsCronogramas.slice();
    }
  }

  filterCronogramaByProperty(property, arrayToFilter, valueToSearch) {
    let filteredCronogramas;

    if (property == 'nombre') {
      filteredCronogramas = arrayToFilter.filter( (cronograma) => {
        if (valueToSearch ==cronograma.nombreAlumno.toLowerCase()) {
          return true;
        }
        return false;
      })
    }

    if (property == 'ID') {
      filteredCronogramas = arrayToFilter.filter( (cronograma) => {
        if (valueToSearch == cronograma.idCronograma.toString()) {
          return true;
        }
        return false;
      })
    }

    
    if (this.currentTabIndex == 1) {
      this.filteredArrayCronogramas = [...this.filteredArrayCronogramas, ...filteredCronogramas];
      //Elimino los duplicados solo para mostrar en pantalla
      this.sinRepetidosCronogramas = this.filteredArrayCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )
      //reordeno
      this.sinRepetidosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })
    }

    if (this.currentTabIndex == 2) {
      this.filteredArrayFinalizadosCronogramas = [...this.filteredArrayFinalizadosCronogramas, ...filteredCronogramas];
      //Elimino los duplicados solo para mostrar en pantalla
      this.sinRepetidosFinalizadosCronogramas = this.filteredArrayFinalizadosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )
      //reordeno
      this.sinRepetidosFinalizadosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })    
    }

    if (this.currentTabIndex == 3) {
      this.filteredArrayCanceladosCronogramas = [...this.filteredArrayCanceladosCronogramas, ...filteredCronogramas];
      //Elimino los duplicados solo para mostrar en pantalla
      this.sinRepetidosCanceladosCronogramas = this.filteredArrayCanceladosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )
      //reordeno
      this.sinRepetidosCanceladosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })    
    }
  }

  nombreAgregado(nombre) {
    let found = this.filteredArrayCronogramas.find( (cronograma) => {
      if (cronograma.nombreAlumno == nombre) {
        return true;
      }
      return false;
    })

    if (found) {
      return true;
    }
    return false;
  }

  idAgregado(id) {
    let found = this.filteredArrayCronogramas.find( (cronograma) => {
      if (cronograma.idCronograma == id) {
        return true;
      }
      return false;
    })

    if (found) {
      return true;
    }
    return false;
  }

  removeNombre(nombre: string) {
    if (this.currentTabIndex == 1) {
      const index = this.selectedNombresChips.get(this.currentTabIndex).map( (v) => {return v.toLowerCase()}).indexOf(nombre.toLowerCase());
      this.selectedNombresChips.get(this.currentTabIndex).splice(index, 1);
      const indexCronograma = this.filteredArrayCronogramas.findIndex( (cronograma) => {
        if (cronograma.nombreAlumno.toLowerCase() == nombre.toLowerCase()) 
          return true;
        return false;
      });

      this.filteredArrayCronogramas.splice(indexCronograma, 1);
      this.sinRepetidosCronogramas = this.filteredArrayCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )

      //reordeno
      this.sinRepetidosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })
    }

    if (this.currentTabIndex == 2) {
      const index = this.selectedNombresChips.get(this.currentTabIndex).map( (v) => {return v.toLowerCase()}).indexOf(nombre.toLowerCase());
      this.selectedNombresChips.get(this.currentTabIndex).splice(index, 1);
      const indexCronograma = this.filteredArrayFinalizadosCronogramas.findIndex( (cronograma) => {
        if (cronograma.nombreAlumno.toLowerCase() == nombre.toLowerCase()) 
          return true;
        return false;
      });

      this.filteredArrayFinalizadosCronogramas.splice(indexCronograma, 1);
      this.sinRepetidosFinalizadosCronogramas = this.filteredArrayFinalizadosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )

      //reordeno
      this.sinRepetidosFinalizadosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })
    }

    if (this.currentTabIndex == 3) {
      const index = this.selectedNombresChips.get(this.currentTabIndex).map( (v) => {return v.toLowerCase()}).indexOf(nombre.toLowerCase());
      this.selectedNombresChips.get(this.currentTabIndex).splice(index, 1);
      const indexCronograma = this.filteredArrayCanceladosCronogramas.findIndex( (cronograma) => {
        if (cronograma.nombreAlumno.toLowerCase() == nombre.toLowerCase()) 
          return true;
        return false;
      });

      this.filteredArrayCanceladosCronogramas.splice(indexCronograma, 1);
      this.sinRepetidosCanceladosCronogramas = this.filteredArrayCanceladosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )

      //reordeno
      this.sinRepetidosCanceladosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })
    }
  }

  removeId(id) {
    if (this.currentTabIndex == 1) {
      const index = this.selectedIdsCronogramasChips.get(this.currentTabIndex).indexOf(id);
      this.selectedIdsCronogramasChips.get(this.currentTabIndex).splice(index, 1);
      const indexCronograma = this.filteredArrayCronogramas.findIndex( (cronograma) => {
        if (cronograma.idCronograma == id) 
          return true;
        return false;
      });
      this.filteredArrayCronogramas.splice(indexCronograma, 1);
      this.sinRepetidosCronogramas = this.filteredArrayCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )

      //reordeno
      this.sinRepetidosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })
    }

    if (this.currentTabIndex == 2) {
      const index = this.selectedIdsCronogramasChips.get(this.currentTabIndex).indexOf(id);

      this.selectedIdsCronogramasChips.get(this.currentTabIndex).splice(index, 1);
      const indexCronograma = this.filteredArrayFinalizadosCronogramas.findIndex( (cronograma) => {
        if (cronograma.idCronograma == id) 
          return true;
        return false;
      });
      this.filteredArrayFinalizadosCronogramas.splice(indexCronograma, 1);
      this.sinRepetidosFinalizadosCronogramas = this.filteredArrayFinalizadosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )

      //reordeno
      this.sinRepetidosFinalizadosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })
    }

    if (this.currentTabIndex == 3) {
      const index = this.selectedIdsCronogramasChips.get(this.currentTabIndex).indexOf(id);

      this.selectedIdsCronogramasChips.get(this.currentTabIndex).splice(index, 1);
      const indexCronograma = this.filteredArrayCanceladosCronogramas.findIndex( (cronograma) => {
        if (cronograma.idCronograma == id) 
          return true;
        return false;
      });
      this.filteredArrayCanceladosCronogramas.splice(indexCronograma, 1);
      this.sinRepetidosCanceladosCronogramas = this.filteredArrayCanceladosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
        ))
      )

      //reordeno
      this.sinRepetidosCanceladosCronogramas.sort( (a, b) => {
        if (a.idCronograma > b.idCronograma) {
          return -1
        }
        return 1;
      })
    }
  }

  removeAllNombres() {
    if (this.currentTabIndex == 1) {
      var indices = [];
      this.selectedNombresChips.get(this.currentTabIndex).forEach( (nombreAlumno) => {
        for(var i=0; i<this.filteredArrayCronogramas.length;i++) {
          if (this.filteredArrayCronogramas[i].nombreAlumno.toLowerCase() === nombreAlumno.toLowerCase()) indices.push(i);
        }
        
        this.filteredArrayCronogramas = this.filteredArrayCronogramas.filter(function(value, index) {
          return indices.indexOf(index) == -1;
        })
      })
  
      this.selectedNombresChips.get(this.currentTabIndex).length = 0;
      
      this.sinRepetidosCronogramas = this.filteredArrayCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
      )))
    }

    if (this.currentTabIndex == 2) {
      var indices = [];
      this.selectedNombresChips.get(this.currentTabIndex).forEach( (nombreAlumno) => {
        for(var i=0; i<this.filteredArrayFinalizadosCronogramas.length;i++) {
          if (this.filteredArrayFinalizadosCronogramas[i].nombreAlumno.toLowerCase() === nombreAlumno.toLowerCase()) indices.push(i);
        }
        
        this.filteredArrayFinalizadosCronogramas = this.filteredArrayFinalizadosCronogramas.filter(function(value, index) {
          return indices.indexOf(index) == -1;
        })
      })
  
      this.selectedNombresChips.get(this.currentTabIndex).length = 0;
      
      this.sinRepetidosFinalizadosCronogramas = this.filteredArrayFinalizadosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
      )))
    }

    if (this.currentTabIndex == 3) {
      var indices = [];
      this.selectedNombresChips.get(this.currentTabIndex).forEach( (nombreAlumno) => {
        for(var i=0; i<this.filteredArrayCanceladosCronogramas.length;i++) {
          if (this.filteredArrayCanceladosCronogramas[i].nombreAlumno.toLowerCase() === nombreAlumno.toLowerCase()) indices.push(i);
        }
        
        this.filteredArrayCanceladosCronogramas = this.filteredArrayCanceladosCronogramas.filter(function(value, index) {
          return indices.indexOf(index) == -1;
        })
      })
  
      this.selectedNombresChips.get(this.currentTabIndex).length = 0;
      
      this.sinRepetidosCanceladosCronogramas = this.filteredArrayCanceladosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
      )))
    }
  }

  removeAllIds() {
    if (this.currentTabIndex == 1) {
      this.selectedIdsCronogramasChips.get(this.currentTabIndex).forEach( (cronograma) => {
        let index = this.filteredArrayCronogramas.findIndex( (c) => {
          if (c.idCronograma == cronograma.idCronograma) 
            return true;
          return false;
        });
        this.filteredArrayCronogramas.splice(index, 1);
      })
      
      this.selectedIdsCronogramasChips.get(this.currentTabIndex).length = 0;
      this.sinRepetidosCronogramas = this.filteredArrayCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
      )))
    }

    if (this.currentTabIndex == 2) {
      this.selectedIdsCronogramasChips.get(this.currentTabIndex).forEach( (cronograma) => {
        let index = this.filteredArrayFinalizadosCronogramas.findIndex( (c) => {
          if (c.idCronograma == cronograma.idCronograma) 
            return true;
          return false;
        });
        this.filteredArrayFinalizadosCronogramas.splice(index, 1);
      })
      
      this.selectedIdsCronogramasChips.get(this.currentTabIndex).length = 0;
      this.sinRepetidosFinalizadosCronogramas = this.filteredArrayFinalizadosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
      )))
    }

    if (this.currentTabIndex == 3) {
      this.selectedIdsCronogramasChips.get(this.currentTabIndex).forEach( (cronograma) => {
        let index = this.filteredArrayCanceladosCronogramas.findIndex( (c) => {
          if (c.idCronograma == cronograma.idCronograma) 
            return true;
          return false;
        });
        this.filteredArrayCanceladosCronogramas.splice(index, 1);
      })
      
      this.selectedIdsCronogramasChips.get(this.currentTabIndex).length = 0;
      this.sinRepetidosCanceladosCronogramas = this.filteredArrayCanceladosCronogramas.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.idCronograma === thing.idCronograma
      )))
    }
  }

  tabChanged(tabChangeEvent): void {
    this.currentTabIndex = tabChangeEvent.index;
  }

  selectedNombre(event) {
    this.selectedNombresChips.get(this.currentTabIndex).push(event.option.viewValue);
    this.nameInput.nativeElement.value = '';
    this.nameForm.controls['nameInput'].disable();
    this.nameForm.controls['nameInput'].enable();
    this.filterCronogramaByProperty(this.filterShowType, this.currentArrayToFilter, event.option.viewValue.toLowerCase());  
  }

  selectedId(event) { 
    this.selectedIdsCronogramasChips.get(this.currentTabIndex).push(event.option.viewValue);
    this.idInput.nativeElement.value = '';
    this.idForm.controls['idInput'].disable();
    this.idForm.controls['idInput'].enable();
    this.filterCronogramaByProperty(this.filterShowType, this.currentArrayToFilter, event.option.viewValue.toLowerCase());
  
  }

  filterCronogramas() {
    this.idsCronogramasFiltered = this.idsCronogramas.filter( (idCronograma) => {
      if (idCronograma.toString().startsWith(this.selectedIdCronogramaFilter[this.currentTabIndex])) {
        return true;
      }
      return false;
    })
  }

  filterNombres() {
    this.nombresFiltered = this.nombres.filter( (nombre) => {
      if (nombre.toLowerCase().startsWith(this.selectedNombreFilter[this.currentTabIndex].toLowerCase())) {
        return true;
      }
      return false;
    })
  }

  changeBorder(property) {
    if (property == 'ID') {
      if(this.selectedIdsCronogramasChips.get(this.currentTabIndex)) {
        if (this.selectedIdsCronogramasChips.get(this.currentTabIndex).length > 0) {
          return '2px solid #43c89d';
        }
        return '1px solid #e0e0e0';
      }
      return '1px solid #e0e0e0';
    }

    if (property == 'nombre') {
      if (this.selectedNombresChips.get(this.currentTabIndex)) {
        if (this.selectedNombresChips.get(this.currentTabIndex).length > 0) {
          return '2px solid #43c89d';
        }
        return '1px solid #e0e0e0';
      }
      return '1px solid #e0e0e0';
    }
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
  }

  onConfirmSchedule(clases, idCronograma, nombreAlumno, idAlumno, direccionPrincipal, direccionAlternativa, direccionPrincipalFormated, direccionAlternativaFormated) {
    let addressesAlumno = [];

    let direccionObject = {
      'idDireccion': direccionPrincipal,
      'direccionFormated': direccionPrincipalFormated
    }
    addressesAlumno.push(direccionObject);

    let direccionObjectCopy = Object.assign({}, direccionObject);

    if (direccionAlternativa != null) {
      direccionObjectCopy.idDireccion = direccionAlternativa;
      direccionObjectCopy.direccionFormated = direccionAlternativaFormated;
  
      addressesAlumno.push(direccionObjectCopy);
    }

    this.dataToConfirm = {
      'clases': clases,
      'idCronograma': idCronograma,
      'nombreAlumno': nombreAlumno,
      'idAlumno': idAlumno,
      'addressesAlumno': addressesAlumno,
      'documento': '',
      'id_DirFisica': '',
      'id_DirPrincipal': '',
      'id_DirAlternativa': '',
      'calle_DirFisica': '',
      'calle_diag_DirFisica': '',
      'calle_a_DirFisica': '',
      'calle_a_diag_DirFisica': '',
      'calle_b_DirFisica': '',
      'calle_b_diag_DirFisica': '',
      'ciudad_DirFisica': '',
      'numero_DirFisica': '',
      'floor_DirFisica': '',
      'observaciones_DirFisica': ''
    };
    this.operation = 'Confirmar';

    this.alumnoService.getInformacionPersonal(this.dataToConfirm.idAlumno).subscribe( (response:Response) => {
      if (response.code == 0) {
        this.direccionDocumento.direccion = response.data;
        this.dataToConfirm.documento = response.data[0].documento;
        this.setDireccionData(response.data[0]);
        this.customModal.setDireccionDefault();
        this.customModal.open();
      } 
    })
  }

  setDireccionData(responseData) {
    this.dataToConfirm.id_DirFisica =responseData.id_DirFisica;
    this.dataToConfirm.id_DirPrincipal = responseData.id_DirPrincipal;
    this.dataToConfirm.id_DirAlternativa = responseData.id_DirAlternativa;
    this.dataToConfirm.calle_DirFisica = responseData.calle_DirFisica;
    this.dataToConfirm.calle_diag_DirFisica = responseData.calle_diag_DirFisica;
    this.dataToConfirm.calle_a_DirFisica = responseData.calle_a_DirFisica;
    this.dataToConfirm.calle_a_diag_DirFisica = responseData.calle_a_diag_DirFisica;
    this.dataToConfirm.calle_b_DirFisica = responseData.calle_b_DirFisica;
    this.dataToConfirm.calle_b_diag_DirFisica = responseData.calle_b_diag_DirFisica;
    this.dataToConfirm.ciudad_DirFisica = responseData.ciudad_DirFisica;
    this.dataToConfirm.floor_DirFisica = responseData.floor_DirFisica;
    this.dataToConfirm.observaciones_DirFisica = responseData.observaciones_DirFisica;
    this.dataToConfirm.numero_DirFisica = responseData.numero_DirFisica;
    this.dataToConfirm.departamento_DirFisica = responseData.departamento_DirFisica;
  }

  confirmSchedule($event) {
    if (this.operation == 'Confirmar') {
      this.confirmarCronograma($event.idCronograma, $event.idAlumno, $event.direccionFisicaInformation, $event.documento, $event.clases);
    } else {
      this.eliminarCronograma($event.idCronograma, $event.idAlumno, $event.motivoDeBaja);
    }
  }

  onCustomModalClose() {
    this.customModal.onClose();
  }

  confirmarCronograma(idCronograma, idAlumno, direccionFisicaInformation, documento, clases) {
    this.cronogramaService.confirmarCronogramaPendiente(idCronograma, idAlumno, direccionFisicaInformation, documento, clases).subscribe( (response: Response) => { 
      if (response.code == 2) {
        this.durationInSeconds = 5;
        response.data = "Para poder confirmar el cronograma debe modificar las siguientes clases ya que alguno de sus datos fue confirmado previamente: " + response.data.join(', ');
      }
      this.showSuccessBanner = false;
      this.customModal.onClose();
      this._snackBar.openFromComponent(SnackbarComponent, {
        duration: this.durationInSeconds * 1100,
        data: response
      });
      this.show_edit = false;
      this.ngOnInit();
      window.scrollTo(0, 0);
    })
  }

  eliminarCronograma(idCronograma, idAlumno, motivoBaja?: string) {
    console.log("operation:: ", this.operation)
    if (this.operation == 'Cancelar') {
      this.cronogramaService.cancelarCronogramaPendiente(idCronograma, idAlumno).subscribe( (response: Response) => {
        this.showSuccessBanner = false;
        this.customModal.onClose();
        this._snackBar.openFromComponent(SnackbarComponent, {
          duration: this.durationInSeconds * 1100,
          data: response
        });
        this.ngOnInit();
        window.scrollTo(0, 0);
      })
    } else {
      this.cronogramaService.cancelarCronogramaActivo(idCronograma, idAlumno, motivoBaja).subscribe( (response: Response) => {
        this.showSuccessBanner = false;
        this.customModal.onClose();
        this._snackBar.openFromComponent(SnackbarComponent, {
          duration: this.durationInSeconds * 1100,
          data: response
        });
        this.ngOnInit();
        window.scrollTo(0, 0);
      })
    }
  }

  onCancelSchedule(idCronograma, nombreAlumno, idAlumno) {
    this.dataToConfirm = {
      'idCronograma': idCronograma,
      'nombreAlumno': nombreAlumno,
      'idAlumno': idAlumno
    };
    this.operation = 'Cancelar';
    this.customModal.open();
  }

  onCancelActiveSchedule(idCronograma, nombreAlumno, idAlumno) {
    this.dataToConfirm = {
      'idCronograma': idCronograma,
      'nombreAlumno': nombreAlumno,
      'idAlumno': idAlumno
    };
    this.operation = 'CancelarActivo';
    this.customModal.open();
  }

  onEditSchedule(cronograma){
    this.show_edit = true;
    this.cronograma_edit = cronograma;
  }

  //Cierro edicion
  closeEditCrono(flag){
    this.show_edit = flag;
    this.ngOnInit();
  }

  //Agregar nueva clase a cronograma
  addClass(cronograma) {
    this.sharedService.setData(cronograma.alumno);
    this.router.navigate(['pendientes/agregarClase/', cronograma.idCronograma]);
  }
}
