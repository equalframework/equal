<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Returns an associative array mapping orm types with possible usages and their descriptors.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'params'        => [],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context          $context
 */
list($context) = [$providers['context']];

$schema = '{
    "string" : {
        "application" : {
            "subusages" : {
                "json" : {},
                "xml" : {},
                "yaml" : {}
            }
        },
        "text" : {
            "subusages" : {
                "plain" : {
                    "variations" : {
                        "short" : {
                            "length_free" : false,
                            "boundary" : false
                        },
                        "small" : {
                            "length_free" : false,
                            "boundary" : false
                        },
                        "medium": {
                            "length_free" : false,
                            "boundary" : false
                        },
                        "long"  : {
                            "length_free" : false,
                            "boundary" : false
                        }
                    }
                },
                "xml"       : {},
                "html"      : {},
                "markdown"  : {},
                "wiki"      : {},
                "json"      : {}
            },
            "length_free" : true,
            "boundary" : true
        },
        "uri" : {
            "subusages" : {
                "url" : {
                    "variations" : {
                        "mailto": {},
                        "payto" : {},
                        "tel"   : {},
                        "http"  : {},
                        "ftp"   : {}
                    }
                },
                "urn" : {
                    "variations" : {
                        "iban": {},
                        "isbn" : {
                            "length" : [
                                10,
                                13
                            ]
                        },
                        "ean"   : {
                            "length" : [
                                13
                            ]
                        }
                    }
                }
            }
        },
        "email" : {},
        "language" : {
            "subusages" : {
                "iso-639" : {
                    "length" : [
                        2,
                        3
                    ]
                }
            }
        },
        "country" : {
            "subusages" : {
                "iso-3166" : {
                    "length" : [
                        2,
                        3
                    ]
                }
            }
        },
        "password" : {},
        "coordinate" : {
            "subusages" : {
                "latitude" : {
                    "variations" : {
                        "decimal" : {},
                        "dms" : {}
                    }
                },
                "longitude" : {
                    "variations" : {
                        "decimal" : {},
                        "dms" : {}
                    }
                }
            }
        },
        "currency" : {
            "subusages" : {
                "iso-4217" : {
                    "variations" : {
                        "alpha" : {},
                        "numeric" : {}
                    }
                }
            }
        },
        "hash" : {
            "subusages" : {
                "md" : {
                    "variations" : {
                        "4" : {
                            "length" : [
                                32
                            ]
                        },
                        "5" : {
                            "length" : [
                                32
                            ]
                        },
                        "6" : {
                            "length" : [
                                64
                            ]
                        }
                    }
                },
                "sha" : {
                    "variations" : {
                        "1" : {
                            "length" : [
                                40
                            ]
                        },
                        "256" : {
                            "length" : [
                                64
                            ]
                        },
                        "512" : {
                            "length" : [
                                128
                            ]
                        }
                    }
                }
            }
        },
        "color" : {
            "subusages" : {
                "css" : {},
                "rgb" : {},
                "rgba" : {},
                "hexadecimal" : {}
            }
        },
        "orm" : {
            "subusages" : {
                "relationship" : {
                    "many2one" : {},
                    "one2many" : {},
                    "one2one"  : {}
                },
                "type" : {
                    "variations" : {
                        "float" : {},
                        "string" : {},
                        "binary" : {},
                        "integer" : {},
                        "time" : {},
                        "datetime" : {},
                        "date" : {}
                    }
                },
                "entity" : {},
                "package" : {}
            }
        }
    },
    "boolean" : {
        "number" : {
            "subusages" : {
                "boolean" : {}
            },
            "length_free" : true,
            "boundary" : true
        }
    },
    "integer" : {
        "number" : {
            "subusages" : {
                "natural" : {},
                "integer" : {
                    "variations" : {
                        "decimal" : {},
                        "hexadecimal" : {},
                        "octal" : {}
                    }
                }
            },
            "length_free" : true,
            "boundary" : true
        },
        "orm" : {
            "subusages" : {
                "object_id" : {}
            }
        }
    },
    "float" : {
        "amount" : {
            "subusages" : {
                "money" : {
                    "length_dim" : 2
                },
                "percent" : {},
                "rate"  :   {}
            }
        },
        "number" : {
            "subusages" : {
                "real" : {
                    "length_dim" : 2
                }
            }
        },
        "length_free" : true,
        "boundary" : true
    },
    "binary" : {
        "image" : {
            "subusages" : {
                "jpeg" : {},
                "gif" :  {},
                "png" :  {},
                "tiff" : {},
                "wepb" : {}
            }
        },
        "application" : {
            "subusages" : {
                "pdf" : {},
                "zip" : {},
                "excel" : {
                    "variations" : {
                        "xlsx" : {},
                        "xls" : {}
                    }
                },
                "word" : {
                    "variations" : {
                        "doc" : {},
                        "docx" : {}
                    }
                },
                "powerpoint" : {
                    "ppt" : {},
                    "pptx" : {}
                }
            }
        },
        "audio" : {
            "subusages" : {
                "aac" : {},
                "webm" : {}
            }
        },
        "video" : {
            "x-msvideo" : {},
            "webm" : {}
        }
    },
    "array" : {
        "array" : {
            "subusages" :  {
                "plain" : {
                    "boundary" : true
                },
                "domain" : {
                    "boundary" : false
                },
                "clause" : {
                    "boundary" : false
                }
            }
        }
    }
}';

$context->httpResponse()
    ->body($schema)
    ->send();
