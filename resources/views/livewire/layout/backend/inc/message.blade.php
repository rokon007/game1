                  @if($message = Session::get('delete'))
						   <div class="alert border-0 bg-danger alert-dismissible fade show py-2">
                             <div class="d-flex align-items-center">
                               <div class="fs-3 text-white"><i class="bi bi-trash-fill"></i>
                               </div>
                               <div class="ms-3">
                                 <div class="text-white">{{$message}}</div>
                                 </div>
                               </div>
                                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                           </div>
						    @endif
							
							@if($message = Session::get('success'))
						   <div x-data="{show:true}" x-int="setTimeout(()=>show=false,4000)" x-show="show"
					         class="alert border-0 bg-success alert-dismissible fade show py-2">
                             <div class="d-flex align-items-center">
                               <div class="fs-3 text-white"><i class="bi bi-check-circle-fill"></i>
                               </div>
                               <div class="ms-3">
                                 <div class="text-white">{{$message}}</div>
                                 </div>
                               </div>
                                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								
                           </div>
						   @endif
                                
						     @if ($errors->any())
						   <div class="alert border-0 bg-warning alert-dismissible fade show py-2">
                              <div class="d-flex align-items-center">
                                <div class="fs-3 text-dark"><i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div class="ms-3">
                                  <div class="text-dark">
								  <ul class="mb-0">
                                      @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                      @endforeach
                                    </ul>
								  
								  </div>
                                  </div>
                                </div>
                             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>
						   @endif